<?php
$testcase = array (
  'description' => 'Assume service states during program downtime',
  'logfile' => 'assumed_states_during_program_downtime_service.log',
  'assumed service states during program downtime #1' => 
  array (
    'assumestatesduringnotrunning' => 'true',
    'start_time' => '1202684400',
    'end_time' => '1202770800',
    'report_type' => 'services',
    'objects' => 
    array (
      0 => 'testhost;PING',
    ),
    'correct' => 
    array (
      'TIME_OK_SCHEDULED' => '0',
      'TIME_OK_UNSCHEDULED' => '86400',
      'TIME_UNDETERMINED_NOT_RUNNING' => '0',
    ),
  ),
  'first state is undetermined' => 
  array (
    'assumestatesduringnotrunning' => '0',
    'start_time' => '1202690000',
    'end_time' => '1202699000',
    'report_type' => 'services',
    'objects' => 
    array (
      0 => 'testhost;PING',
    ),
    'correct' => 
    array (
      'TIME_OK_UNSCHEDULED' => '3800',
      'TIME_UNDETERMINED_NOT_RUNNING' => '5200',
      'subs' => 
      array (
        'testhost;PING' => 
        array (
          'TIME_OK_UNSCHEDULED' => '3800',
          'TIME_UNDETERMINED_NOT_RUNNING' => '5200',
        ),
      ),
    ),
  ),
);
