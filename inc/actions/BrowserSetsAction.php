<?php
/**
 * "BrowserSets" action.
 *
 * @author Brett Fattori, 2013
 * @since 1.0.0
 * @package TestSwarm
 */
class BrowserSetsAction extends Action {

	/**
	 * @actionNote This action takes no parameters.
	 */
	public function doAction() {
		$conf = $this->getContext()->getConf();
		$db = $this->getContext()->getDB();
		$request = $this->getContext()->getRequest();

		$data = array(
			'info' => array(),
		);

		foreach ( $conf->browserSets as $browserSet => $browsers ) {
            $data['info'][] = $browserSet;
		}

		$this->setData( $data );
	}
}
