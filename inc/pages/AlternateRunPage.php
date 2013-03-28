<?php
/**
 * "Alternaterun" page.
 *
 * @author Brett Fattori, 2013
 * @since 0.1.0
 * @package TestSwarm
 */

class AlternaterunPage extends Page {

	protected function initContent() {
        $this->setFrameOptions(false);

		$browserInfo = $this->getContext()->getBrowserInfo();
		$conf = $this->getContext()->getConf();
		$request = $this->getContext()->getRequest();

		$uaData = $browserInfo->getUaData();

		$runToken = null;
		if ( $conf->client->requireRunToken ) {
			$runToken = $request->getVal( "run_token" );
			if ( !$runToken ) {
				throw new SwarmException( "This swarm has restricted access to join the swarm." );
			}
		}

		$this->setTitle( "Alternate Run" );
		$this->bodyScripts[] = swarmpath( "js/alternate.js?" . time() );

		$client = Client::newFromContext( $this->getContext(), $runToken );

		$html = '<script>'
			. 'SWARM.client_id = ' . json_encode( $client->getClientRow()->id ) . ';'
			. 'SWARM.run_token = ' . json_encode( $runToken ) . ';'
			. '</script>';

		$html .= "<div>Alternate runner to handle clients that do not support window.opener.postMessage()</div>";

		return $html;
	}
}

