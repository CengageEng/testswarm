<?php
/**
 * "Scores" page.
 *
 * @author John Resig, 2008-2011
 * @author Jörn Zaefferer, 2012
 * @since 0.1.0
 * @package TestSwarm
 */

class ScoresPage extends Page {

	public function execute() {
		$action = ScoresAction::newFromContext( $this->getContext() );
		$action->doAction();

		$this->setAction( $action );
		$this->content = $this->initContent();
	}

	protected function initContent() {
		$this->setTitle( "Scores" );
		$scores = $this->getAction()->getData();

		$html = '<blockquote><p>The score is the aggregated number of tests ran by all clients with the same name.</p></blockquote>'
		 . '<table class="table table-striped">'
		 . '<thead><tr><th class="span1">#</th><th>Client name</th><th class="span2">Score</th></tr></thead>'
		 . '<tbody>';

		foreach ( $scores as $item ) {
			$html .= '<tr><td class="num">' . htmlspecialchars( number_format( $item["position"] ) ) . '</td>'
				. '<td><a href="' . htmlspecialchars( swarmpath( "client/{$item["name"]}" ) ) . '">' . htmlspecialchars( $item["name"] ) . '</a></td>'
				. '<td class="num">' . htmlspecialchars( number_format( $item["score"] ) ) . '</td></tr>';
		}
		$html .= '</tbody></table>';

		return $html;
	}

}
