<?php

/**
 * Execute commands on ORM objects
 */
class Cmd_Controller extends Ninja_Controller {

	/**
	 * Show a form for submitting a command on a single object
	 */
	public function index() {
		// todo mayi
		$this->template->content = $this->add_view('cmd/index');
		$this->template->disable_refresh = true;
		$this->template->content->error = false;
		$command = $this->input->get('command');
		$table = $this->input->get('table');
		$object_key = $this->input->get('object');

		try {
			$pool = ObjectPool_Model::pool($table);
		} catch(ORMException $e) {
			request::send_header(400);
			$this->template->content->error = $e->getMessage();
			return;
		}


		$object = $pool->fetch_by_key($object_key);
		if($object === false) {
			request::send_header(400);
			$this->template->content->error = "Could not find $table object '$object_key'";
			return;
		}
		$this->template->content->object = $object;
		$this->template->content->table = $table;
		$this->template->content->command = $command;

		$commands = $object->list_commands();
		if(!array_key_exists($command, $commands)) {
			request::send_header(400);
			$error_message = "Tried to submit command '$command' on table '$table' but that command does not exist for that kind of objects. Aborting without any commands applied";
			op5log::instance('ninja')->log('warning', $error_message);
			$this->template->content->error = "Could not find object '$error_message'";
			return;
		}
		if(isset($commands[$command]['redirect']) && $commands[$command]['redirect']) {
			// All commands that have the 'redirect' flag set
			// wants us to skip the regular command form view
			// and provide its own. For example: locate host
			// on map (Nagvis)
			$result = $object->$command();
			if(isset($result['status'])
					&& $result['status']
					&& isset($result['redirect'])
					&& $result['redirect']) {
				return url::redirect($result['redirect']);
			}
		}
		$this->template->content->command_info = $commands[$command];
	}

	/**
	 * Send a command for a specific object
	 */
	public function obj($resp_type = 'html') {
		// TODO Don't use ORMException in this code...

		$template = $this->template->content = $this->add_view('cmd/exec');

		$command = $this->input->post('command', false);
		$table = $this->input->post('table', false);
		$key = $this->input->post('object', false);

		try {
			// validate input parameters presence
			$errors = array();
			if($command == false) {
				$errors[] = 'Missing command (the c parameter)';
			}
			if($table == false) {
				$errors[] = 'Missing table (the t parameter)';
			}
			if($key == false) {
				$errors[] = 'Missing object name (the o parameter)';
			}

			if($errors) {
				throw new ORMException(implode("<br>", $errors));
			}

			// validate table name
			$pool = ObjectPool_Model::pool($table);

			// validate object by primary key
			/* @var $object Object_Model */
			$object = $pool->fetch_by_key($key);
			if($object === false) {
				throw new ORMException("Could not find object '$key'", $table, false);
			}

			// validate command
			$commands = $object->list_commands(true);
			if(!array_key_exists($command, $commands)) {
				throw new ORMException("Tried to submit command '$command' but that command does not exist for that kind of objects. Aborting without any commands applied", $table, false);
			}

			// Unpack params
			$params = array();
			foreach($commands[$command]['params'] as $pname => $pdef) {
				$params[intval($pdef['id'])] = $this->input->post($pname, null);
			}

			// Depend on order of id instead of order of occurance
			ksort($params);

			// Don't set $this->template->content directly, since command might throw exceptions
			$command_template = $this->add_view($commands[$command]['view']);
			$result = call_user_func_array(array($object, $command), $params);
			$command_template->result = $result;
			if(isset($result['status']) && !$result['status']) {
				request::send_header(400);
			}
			$this->template->content = $command_template;
		} catch(ORMException $e) {
			request::send_header(400);
			$error_message = $e->getMessage();
			op5log::instance('ninja')->log('warning', $error_message);
			if(request::is_ajax()) {
				return json::fail(array('error' => $error_message));
			}
			$template->result = false;
			$template->error = $error_message;
		}
	}
}
