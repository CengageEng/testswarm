<?php

/*
	PLAN:

	Large 2-column .well with headings for each unique client name

	under each heading another .well with inside the icon blocks for
	each client (with displayInfo labels), like on classic UserPage.

	Each heading also has a /clients/name permalink (which is just a filter to
	hide the rest and give it full width).

	Under each heading, above the mini-swarm, we show the score.

	On top, sort by: [ updated (default), name, score ]

	In addition:
	For each online client we display:
	- Connected <time ago>
	- Last ping <time ago>
	- (NEW) What it is currently doing
	- (NEW) What it has done ([more...](1))

	{1} /client/id
	Will show complete history. Basically like the js-log on RunPage,
	with links to everything.

*/

/*
	This replaces: ScoresAction/ScoresPage and UserPage.
*/
