<?php


/**
 * Autogenerated class DashboardSet_Model
 *
 * @todo: documentation
 */
class DashboardSet_Model extends BaseDashboardSet_Model {

	public function mayi_resource () {
		return "monitor.system.dashboards";
	}

	public function get_auth_filter() {
		$user = Auth::instance()->get_user();
		$filter = new LivestatusFilterAnd();
		$filter->add($this->filter);

		$auth_filter = new LivestatusFilterOr();
		$auth_filter->add(new LivestatusFilterMatch('read_perm', $user->get_permission_regexp(), '~~'));

		/* For now, auth can be either by read_perm, or the user has created the dashboard */
		$auth_filter->add(new LivestatusFilterMatch('username', $user->get_username(), '='));

		$filter->add($auth_filter);
		return $filter;
	}
}
