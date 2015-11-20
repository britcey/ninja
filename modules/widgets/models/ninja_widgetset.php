<?php

require_once( dirname(__FILE__).'/base/baseninja_widgetset.php' );

/**
 * Autogenerated class NinjaWidgetSet_Model
 *
 * @todo: documentation
 */
class Ninja_WidgetSet_Model extends BaseNinja_WidgetSet_Model {
	/**
	 * Return resource name of this object
	 * @return string
	 */
	public function mayi_resource() {
		return "ninja.widgets";
	}
	/**
	 * apply some extra filters to match for the authentication.
	 */
	protected function get_auth_filter() {
		$username = Auth::instance()->get_user()->username;

		$result_filter = new LivestatusFilterAnd();
		$result_filter->add($this->filter);
		$result_filter->add(new LivestatusFilterMatch('username', $username));

		return $result_filter;
	}
}