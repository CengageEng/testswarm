<?php
/**
 * Project action.
 *
 * @author Timo Tijhof, 2012
 * @since 1.0.0
 * @package TestSwarm
 */
class ProjectAction extends Action {

	/**
	 * @actionParam string item: Project ID.
	 */
	public function doAction() {
		$conf = $this->getContext()->getConf();
		$db = $this->getContext()->getDB();
		$request = $this->getContext()->getRequest();

		$projectID = $request->getVal( 'item' );
		if ( !$projectID ) {
			$this->setError( 'missing-parameters' );
			return;
		}

		$start = intval( base_convert( $request->getVal( 'start', 1 ), 36, 10 ) );
		$dir = $request->getVal( 'dir', 'desc' );
		$limit = $request->getInt( 'limit', 25 );
		if ( $start < 1
			|| !in_array( $dir, array( 'asc', 'desc' ) )
			|| $limit < 1
			|| $limit > 100
		) {
			$this->setError( 'invalid-input', compact( 'start', 'dir', 'limit' ) );
			return;
		}

		// Get project info
		$projectRow = $db->getRow(str_queryf(
			'SELECT
				id,
				display_title,
				site_url,
				updated,
				created
			FROM projects
			WHERE id = %s;',
			$projectID
		));
		if ( !$projectRow ) {
			$this->setError( 'invalid-input', 'Project does not exist' );
			return;
		}

		// Get list of jobs
		$jobRows = $db->getRows(str_queryf(
			'SELECT
				id,
				name
			FROM
				jobs
			WHERE project_id = %u
			ORDER BY id DESC
			LIMIT 15;',
			$projectID
		));
		$jobs = array();
		if ( $jobRows ) {

		}

		$this->setData( array(
			'projectInfo' => $projectRow,
			'jobs' => $jobs,
		));
	}

	private function stash() {

		// List of all user agents used in recent jobs
		// This is as helper allow creating proper gaps when iterating
		// over jobs.
		$userAgents = array();

		$jobRows = $db->getRows(str_queryf(
			'SELECT
				id,
				name
			FROM
				jobs
			WHERE project_id = %u
			ORDER BY id DESC
			LIMIT 15;',
			$userID
		));
		if ( $jobRows ) {
			$uaRunStatusStrength = array_flip(array(
				'passed',
				'new',
				'progress',
				'timedout',
				'failed',
				'error', // highest priority
			));

			foreach ( $jobRows as $jobRow ) {
				$jobID = intval( $jobRow->id );

				$jobAction = JobAction::newFromContext( $this->getContext()->createDerivedRequestContext(
					array(
						'item' => $jobID,
					),
					'GET'
				) );
				$jobAction->doAction();
				if ( $jobAction->getError() ) {
					$this->setError( $jobAction->getError() );
					return;
				}
				$jobActionData = $jobAction->getData();

				// Add user agents array of this job to the overal user agents list.
				// php array+ automatically fixes clashing keys. The values are always the same
				// so it doesn't matter whether or not it overwrites.
				$userAgents += $jobActionData['userAgents'];

				// The summerized status for each user agent run
				// of this job. e.g. if all are new except one,
				// then it will be on progress, if all are complete
				// then the worst failure is put in the summary
				$uaSummary = array();

				$uaNotNew = array();
				$uaHasIncomplete = array();
				$uaStrongestStatus = array();

				foreach ( $jobActionData['runs'] as $run ) {
					foreach ( $run['uaRuns'] as $uaID => $uaRun ) {
						if ( $uaRun['runStatus'] !== 'new' && !in_array( $uaID, $uaNotNew ) ) {
							$uaNotNew[] = $uaID;
						}
						if ( $uaRun['runStatus'] === 'new' || $uaRun['runStatus'] === 'progress' ) {
							if ( !in_array( $uaID, $uaHasIncomplete ) ) {
								$uaHasIncomplete[] = $uaID;
							}
						}
						if ( !isset( $uaStrongestStatus[$uaID] )
							|| $uaRunStatusStrength[$uaRun['runStatus']] > $uaRunStatusStrength[$uaStrongestStatus[$uaID]]
						) {
							$uaStrongestStatus[$uaID] = $uaRun['runStatus'];
						}
						$uaSummary[$uaID] = !in_array( $uaID, $uaNotNew )
							? 'new'
							: ( in_array( $uaID, $uaHasIncomplete )
								? 'progress'
								: $uaStrongestStatus[$uaID]
							);
					}
				}

				$recentJobs[] = array(
					'id' => $jobID,
					'name' => $jobRow->name,
					'url' => swarmpath( "job/$jobID", 'fullurl' ),
					'uaSummary' => $uaSummary,
				);
			}
		}

		uasort( $userAgents, 'BrowserInfo::sortUaData' );

		$this->setData(array(
			'recentJobs' => $recentJobs,
			'uasInJobs' => $userAgents,
		));
	}
}
