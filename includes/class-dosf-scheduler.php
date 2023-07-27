<?php

define('DOSF_SCHEDULE_INTERVAL_MON_KEY_NM','dosf_monitor_schedule_interval');
define('DOSF_SCHEDULE_INTERVAL_EWMQ_PROC_KEY_NM','dosf_ewmq_proc_schedule_interval');
define('DOSF_HOOK_SCHEDULED_EVENT_MONITOR','dosf_scheduler_monitor');
define('DOSF_HOOK_SCHEDULED_EVENT_EWMQ_PROCESS','dosf_scheduler_ewmq_process');

class Wp_Dosf_Scheduler {
    function __construct(){
        add_action( 'init', [$this,'prepare_wp_scheduler'] );

        add_action( DOSF_HOOK_SCHEDULED_EVENT_MONITOR, 'Wp_Dosf_Admin::verify_certs_expiration' );

		add_action( DOSF_HOOK_SCHEDULED_EVENT_EWMQ_PROCESS, 'Wp_Dosf_Admin::process_certs_expiration_queue' );
    }

    public function set_schedules( $s ){
        $plus_options = get_option(DOSF_WP_OPT_NM_PLUS_OPTIONS);

        if( $plus_options === false ) return $s;

        $interval = $plus_options['monitor-expire-interval'];

        if( !isset( $interval ) || empty( $interval ) ) return $s;
        

        $s[DOSF_SCHEDULE_INTERVAL_MON_KEY_NM] = array(
            'interval' => $interval * 60 * 60 * 24,
            'display'  => "Intervalo del planificador de DOSF ($interval días)"
        );

        $interval = apply_filters('dosf_scheduler_interval_for_process_ewmq',120);

        $s[DOSF_SCHEDULE_INTERVAL_EWMQ_PROC_KEY_NM] = array(
            'interval' => $interval,
            'display'  => "Intervalo del procesador EWMQ de DOSF ($interval segundos)"
        );

        return $s;
    }

    public function prepare_wp_scheduler(){
        // the actual hook to register new custom schedule

        add_filter( 'cron_schedules', [$this,'set_schedules'] );

        // schedule custom event
        if( !wp_next_scheduled( DOSF_HOOK_SCHEDULED_EVENT_MONITOR ) )
        {
            wp_schedule_event( time(), DOSF_SCHEDULE_INTERVAL_MON_KEY_NM, DOSF_HOOK_SCHEDULED_EVENT_MONITOR );
        }

    }
}