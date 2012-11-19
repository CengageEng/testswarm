<?php
/**
 * Cleanup action
 *
 * @author John Resig, 2008-2011
 * @since 0.1.0
 * @package TestSwarm
 */
class CleanupAction extends Action {

	/**
	 * @actionNote This action takes no parameters.
	 */
	public function doAction() {
		$browserInfo = $this->getContext()->getBrowserInfo();
		$db = $this->getContext()->getDB();
		$conf = $this->getContext()->getConf();
		$request = $this->getContext()->getRequest();

		$resetTimedoutRuns = 0;

		// Get clients that are considered disconnected (not responding to the latest pings).
		// Then mark the runresults of its active runs as timed-out, and reset those runs so
		// they become available again for different clients in GetrunAction.

		$clientMaxAge = swarmdb_dateformat( time() - ( $conf->client->pingTime + $conf->client->pingTimeMargin ) );

		$rows = $db->getRows(str_queryf(
			"SELECT
				runresults.id as id
			FROM
				runresults
			INNER JOIN clients ON runresults.client_id = clients.id
			WHERE runresults.status = 1
			AND   clients.updated < %s;",
			$clientMaxAge
		));

		if ($rows) {
			$resetTimedoutRuns = count($rows);
			foreach ($rows as $row) {
				// Reset the run
				$db->query(str_queryf(
					"UPDATE run_useragent
					SET
						status = 0,
						results_id = NULL
					WHERE results_id = %u;",
					$row->id
				));

				// Update status of the result
				$db->query(str_queryf(
					"UPDATE runresults
					SET status = %s
					WHERE id = %u;",
					ResultAction::$STATE_LOST,
					$row->id
				));
			}
		}

		$this->setData(array(
			"resetTimedoutRuns" => $resetTimedoutRuns,
		));
	}
}

