<?php
/**
 * "Projects" page.
 *
 * @author Timo Tijhof, 2012
 * @since 1.0.0
 * @package TestSwarm
 */

class ProjectsPage extends Page {

	public function execute() {
		$action = ProjectsAction::newFromContext( $this->getContext() );
		$action->doAction();

		$this->setAction( $action );
		$this->content = $this->initContent();
	}

	protected function initContent() {
		$this->setTitle( 'Projects' );

		$projects = $this->getAction()->getData();

		$html = '<blockquote><p>Below is an overview of all registered projects,'
			. ' sorted alphabetically by name.</p></blockquote>'
			. '<table class="table table-striped">'
			. '<thead><tr>'
				. '<th>Project name</th>'
				. '<th class="span2">Latest job</th>'
			. '</tr></thead>'
			. '<tbody>';

		foreach ( $projects as $project ) {
			$html .= '<tr>';
			$html .= '<td><a href="' . htmlspecialchars( swarmpath( "project/{$project['id']}" ) ) . '">' . htmlspecialchars( $project['name'] ) . '</a></td>';
			if ( $project['jobLatest'] ) {
				$html .= '<td><a href="' . htmlspecialchars( swarmpath( "job/{$project['jobLatest']['id']}" ) ) . '">Job #' . htmlspecialchars( $project['jobLatest']['id'] ) . '</a></td>';
			} else {
				$html .= '<td><em>--</em></td>';
			}
			$html .= '</tr>';
		}
		$html .= '</tbody></table>';

		return $html;
	}

}
