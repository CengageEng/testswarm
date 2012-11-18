<?php
/**
 * Projects action.
 *
 * @author Timo Tijhof, 2012
 * @since 1.0.0
 * @package TestSwarm
 */
class ProjectsAction extends Action {

	/**
	 * @actionParam string sort: [optional] What to sort the results by.
	 * Must be one of "name", "id" or "creation". Defaults to "name".
	 * @actionParam string sort_order: [optional]
	 * Must be one of "asc" (ascending" or "desc" (decending). Defaults to "asc".
	 */
	public function doAction() {
		$db = $this->getContext()->getDB();
		$request = $this->getContext()->getRequest();

		$filterSort = $request->getVal( 'sort', 'name' );
		$filterSortOrder = $request->getVal( 'sort_order', 'asc' );

		if ( !in_array( $filterSort, array( 'name', 'id', 'creation' ) ) ) {
			$this->setError( 'invalid-input', "Unknown sort `$filterSort`." );
			return;
		}

		if ( !in_array( $filterSortOrder, array( 'asc', 'desc' ) ) ) {
			$this->setError( 'invalid-input', "Unknown sort order `$filterSortOrder`." );
			return;
		}

		$filterSortOrderQuery = '';
		switch ( $filterSortOrder ) {
			case 'asc':
				$filterSortOrderQuery = 'ASC';
				break;
			case 'desc':
				$filterSortOrderQuery = 'DESC';
				break;
		}

		$filterSortQuery = '';
		switch ( $filterSort ) {
			case 'name':
				$filterSortQuery = "ORDER BY display_title $filterSortOrderQuery";
				break;
			case 'id':
				$filterSortQuery = "ORDER BY id $filterSortOrderQuery";
				break;
			case 'creation':
				$filterSortQuery = "ORDER BY created $filterSortOrderQuery";
				break;
		}

		$projects = array();
		$projectRows = $db->getRows(
			"SELECT
				id,
				display_title,
				created
			FROM projects
			$filterSortQuery;"
		);

		if ( $projectRows ) {
			foreach ( $projectRows as $projectRow ) {
				$jobRow = $db->getRow(str_queryf(
					'SELECT * FROM jobs WHERE project_id = %u ORDER BY id DESC LIMIT 1;',
					$projectRow->id
				));
				$project = array(
					'id' => $projectRow->id,
					'name' => $projectRow->display_title,
					'jobLatest' => $jobRow ? JobAction::getJobInfoFromId( $db, $jobRow->id ) : false,
				);
				self::addTimestampsTo( $project, $projectRow->created, 'created' );
				$projects[] = $project;
			}
		}

		$this->setData( $projects );
	}
}
