CREATE DEFINER=`root`@`localhost` PROCEDURE `proc_clear_data`()
    COMMENT 'This procedure clears data from the data entry tables. Be careful! Use it wisely'
BEGIN
	TRUNCATE participants;
	TRUNCATE participant_equipment;
	TRUNCATE participant_readiness;
	TRUNCATE participant_readiness_responses;
	TRUNCATE pt_batches;
	TRUNCATE pt_batch_tube;
	TRUNCATE pt_calendar;
	TRUNCATE pt_data_log;
	TRUNCATE pt_data_submission;
	TRUNCATE pt_data_submission_reagent;
	TRUNCATE pt_equipment_results;
	TRUNCATE pt_labs;
	TRUNCATE pt_labs_results;
	TRUNCATE pt_panel_tracking;
	TRUNCATE pt_round;
	TRUNCATE pt_samples;
	TRUNCATE pt_testers;
	TRUNCATE pt_testers_result;
	TRUNCATE pt_tubes;
	TRUNCATE sample_conditions;
	TRUNCATE test_result;
END