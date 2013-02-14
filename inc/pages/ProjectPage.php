<?php
/**
 * "Project" page.
 *
 * @author John Resig, 2008-2011
 * @author Jörn Zaefferer, 2012
 * @author Timo Tijhof, 2012-2013
 * @since 1.0.0
 * @package TestSwarm
 */
class ProjectPage extends Page {
	public function execute() {
		$action = ProjectAction::newFromContext( $this->getContext() );
		$action->doAction();

		$this->setAction( $action );
		$this->content = $this->initContent();
	}

	protected function initContent() {

		$this->setTitle( "Project" );

		$html = "";

		$error = $this->getAction()->getError();
		$data = $this->getAction()->getData();
		if ( $error ) {
			$html .= html_tag( "div", array( "class" => "alert alert-error" ), $error["info"] );
			return $html;
		}

		$this->setSubTitle( $data['info']['display_title'] );

		if ( !count( $data['jobs'] ) ) {

			$html .= '<div class="alert alert-info">No jobs found.</div>';

		} else {

			$html .= '<h2>Jobs</h2><p class="swarm-pagination">';
			if ( $data['pagination']['prev'] ) {
				$html .= html_tag_open( 'a', array(
					'class' => 'swarm-pagination-prev',
					'href' => $data['pagination']['prev']['viewUrl'],
				) ) . '&larr;&nbsp;prev</a>';
			} else {
				$html .= '<span class="swarm-pagination-prev swarm-pagination-disabled" title="No previous page">&larr;&nbsp;prev</span>';
			}
			if ( $data['pagination']['next'] ) {
				$html .= html_tag_open( 'a', array(
					'class' => 'swarm-pagination-next',
					'href' => $data['pagination']['next']['viewUrl'],
				) ) . 'next&nbsp;&rarr;</a>';
			} else {
				$html .= '<span class="swarm-pagination-next swarm-pagination-disabled" title="No next page">next&nbsp;&rarr;</span>';
			}
			$html .= '</p>';

			$html .= '<table class="table table-bordered swarm-results">';
			$html .= '<thead>';
			$html .= JobPage::getUaHtmlHeader( $data['userAgents'] );
			$html .= '</thead><tbody>';

			foreach ( $data['jobs'] as $job ) {
				$html .= JobPage::getJobHtmlRow( $job, $data['userAgents'] );
			}

			$html .= '</tbody></table>';
		}

		return $html;
	}
}
