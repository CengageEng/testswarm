<?php
/**
 * "User" action.
 *
 * @author Timo Tijhof, 2012
 * @since 1.0.0
 * @package TestSwarm
 */
class UserAction extends Action {

	/**
	 * @actionParam int item: Username.
	 */
	public function doAction() {
		$conf = $this->getContext()->getConf();
		$db = $this->getContext()->getDB();
		$request = $this->getContext()->getRequest();

		$userName = $request->getVal( "item" );
		if ( !$userName ) {
			$this->setError( "missing-parameters" );
			return;
		}

		$userID = $db->getOne(str_queryf( "SELECT id FROM users WHERE name = %s;", $userName ));
		$userID = intval( $userID );
		if ( !$userID ) {
			$this->setError( "invalid-input", "User does not exist" );
			return;
		}

		// Active clients
		$activeClients = array();

		$clientRows = $db->getRows(str_queryf(
			"SELECT
				useragent_id,
				useragent,
				updated,
				created
			FROM
				clients
			WHERE user_id = %u
			AND   updated >= %s
			ORDER BY created DESC;",
			$userID,
			swarmdb_dateformat( time() - ( $conf->client->pingTime + $conf->client->pingTimeMargin ) )
		));

		if ( $clientRows ) {
			foreach ( $clientRows as $clientRow ) {
				$bi = BrowserInfo::newFromContext( $this->getContext(), $clientRow->useragent );

				$activeClient = array(
					'uaID' => $bi->getSwarmUaID(),
					'uaRaw' => $bi->getRawUA(),
					'uaData' => $bi->getUaData()
				);
				self::addTimestampsTo( $activeClient, $clientRow->created, "connected" );
				self::addTimestampsTo( $activeClient, $clientRow->updated, "pinged" );
				$activeClients[] = $activeClient;
			}
		}


		$this->setData(array(
			"userName" => $userName,
			"activeClients" => $activeClients,
			#"recentJobs" => $recentJobs,
			#"uasInJobs" => $userAgents,
		));
	}
}
