<?php
/**
 * "Cleanup" action (previously WipeAction)
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

		// Clean up old jobs
		$db->query(str_queryf(
		    "DELETE FROM jobs WHERE created < now() - INTERVAL %u " . $conf->database->maxInterval .";",
		     $conf->database->maxAge
		));

		// Clean up old results
		$db->query(str_queryf(
		    "DELETE FROM runresults WHERE created < now() - INTERVAL %u " . $conf->database->maxInterval .";",
		     $conf->database->maxAge
		));

		// Clean up old runs
		$db->query(str_queryf(
		    "DELETE FROM runs WHERE created < now() - INTERVAL %u " . $conf->database->maxInterval .";",
		     $conf->database->maxAge
		));

		// Clean up old run user agents
		$db->query(str_queryf(
		    "DELETE FROM run_useragent WHERE created < now() - INTERVAL %u " . $conf->database->maxInterval .";",
		     $conf->database->maxAge
		));

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
			foreach ($rows as $row) {
				// Reset the run
				$ret = $db->query(str_queryf(
					"UPDATE run_useragent
					SET
						status = 0,
						results_id = NULL
					WHERE results_id = %u;",
					$row->id
				));

				// If the previous UPDATE query failed for whatever
				// reason, don't do the below query as that will lead
				// to data corruption (results with state LOST must never
				// be referenced from run_useragent.results_id).
				if ( $ret ) {
					// Update status of the result
					$ret = $db->query(str_queryf(
						"UPDATE runresults
						SET status = %s
						WHERE id = %u;",
						ResultAction::$STATE_LOST,
						$row->id
					));
				}

				if ( $ret ) {
					$resetTimedoutRuns++;
				}
			}
		}

		$this->setData(array(
			"resetTimedoutRuns" => $resetTimedoutRuns,
		));
	}
}

