<?php
// Calculate the best possible next guess for Mastermind

// (c) Dave Hulbert 2011

// secret code:
//	 6 5 4 3

// guess should be 4 digits, 1 to 6
// matches should be 0-3 for how many are correct & in the same place
//                   0-4 for how many are correct & in the wrong place

$previous_guesses = array(
	// guess      matches
	'1 2 3 4' => '0` 2',
	//'0 1 2 5' => '0 1',
	//'0 6 4 3' => '2 1',
	//'0 3 4 4' => '2 0',
);


$colors = range(0, 6);
$holes = range(0, 3);

$permutations = pow(count($colors), count($holes));

$guesses = array();

function get_best_move($previous_guesses) {
	$possible_guesses = get_remaining_solutions($previous_guesses);
	$best_move = null;
	$lowest_worst_case = 999999999;
	$best_spreads = null;

	foreach ($possible_guesses as $guess) {
		echo "Checking spread for $guess: ";

		$mark_spreads = get_mark_spreads($guess, $previous_guesses);
		//print_r($mark_spreads);
		$highest_mark_group = max($mark_spreads);
		echo " biggest group: $highest_mark_group ";

		if ($highest_mark_group == 0) {
			echo " Found solution.";
			break;
		}

		echo $mark_spreads['4 0'];
		if ($mark_spreads['4 0'] == 0) {
			echo " no valid solution\n";
			continue;
		}

		echo "\n";

		if ($highest_mark_group < $lowest_worst_case) {
			$lowest_worst_case = $highest_mark_group;
			$best_move = $guess;
			$best_spreads = $mark_spreads;
		}
	}


	print_r($best_spreads);

	return $best_move;
}

function get_remaining_solutions($previous_guesses, $exact = false) {
	$perms = get_all_permutations();
	$solutions = array();

	$keys = array_keys($previous_guesses);

	foreach ($perms as $perm) {
		if (in_array($perm, $keys)) {
			//echo "already guessed\n";
			continue;			
		}

		if (is_valid($perm, $previous_guesses, $exact)) {
			$solutions[] = $perm;
		}
	}
	//echo "Got " . count($solutions) . " remaining solutions\n";
	return $solutions;
}

function is_valid($perm, $previous_guesses, $exact) {
	foreach ($previous_guesses as $guess => $mark) {
		$matches = perm_matches($perm, $guess, $mark, $exact);
		//echo $matches ? " OK\n" : " FAIL\n";
		if (!$matches) {
			return false;
		}
	}

	return true;
}

function perm_matches($perm, $guess, $mark, $exact) {
	//echo "Testing $perm against $guess which got $mark: ";
	$perm = explode(' ', $perm);
	$guess = explode(' ', $guess);
	list($blacks_required, $whites_required) = explode(' ', $mark);

	$same_colors = count(array_intersect($perm, $guess));
	if (($same_colors - $blacks_required) < $whites_required ) {
		//echo impode('', $perm) . " has $same_colors instea of $whites_required whites\n";
		return false;
	}

	if ($exact && ($same_colors - $blacks_required) != $whites_required) {
		return false;
	}

	$exact_matches = 0;
	foreach ($perm as $i => $perm_color) {
		if ($perm_color == $guess[$i]) {
			$exact_matches++;
		}
	}

	if ($exact && ($exact_matches != $blacks_required)) {
		return false;
	}

	//echo impode('', $perm) . " has $same_colors instea of $whites_required whites\n";
	//echo "blacks $exact_matches >= $blacks_required\n";
	return ($exact_matches >= $blacks_required);
}

function get_all_permutations() {
	global $colors, $holes;

	$perms = $colors;

	$i = 1;
	for ($i = 1; $i < count($holes); $i++) {
		$old_perms = $perms;
		$perms = array();
		
		foreach ($old_perms as $old_perm) {
			foreach ($colors as $color) {			
				$perms[] = $old_perm . ' ' . $color;
			}
		}
	}

	return $perms;
}

function get_mark_spreads($guess, $previous_guesses) {
	// for each possible mark, see how many solutions there are
	$mark = 0;
	$spreads = array();
	foreach (array(0, 1, 2, 3, 4) as $black) {
		foreach (array(0, 1,2,3,4) as $white) {
			if ($black + $white > 4) {
				continue;
			}
			$mark++;

			$guesses = $previous_guesses;
			$guesses[$guess] = $black.' '.$white;
			$spreads[$black.' '.$white] = count(get_remaining_solutions($guesses, true));
		}
	}

	return $spreads;
}


//print_r(get_all_permutations());

var_dump(get_best_move($previous_guesses));

