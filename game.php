<?php
//set arguments
array_shift($argv);
$arguments = $argv;
checkArguments($arguments);
$pyramid_rows = $arguments[0];
$max_sticks = $arguments[1];
//set screen resolution
$screen_height = exec('tput cols');
$screen_width = exec('tput lines');

print 'TIME TO DUEL!' . PHP_EOL;
$game_table = [];
//initiate starting pyramid
$game_table = CreatePyramid($pyramid_rows, $game_table);
//setting game play loop
$turn = 0;
$star_border = '***********************************';
$running = true;
while ($running) {
    if ($turn === 0) {
        print($star_border . PHP_EOL);
        playersTurn($game_table);
        print($star_border . PHP_EOL);

    } else {
        print($star_border . PHP_EOL);
        AITurnPair($game_table);
        print($star_border . PHP_EOL);
    }
    checkWin($game_table);
    $turn = ++$turn % 2;
}
//Artificial Intelligence Random Pair numbers
function AITurnPair(&$game_table)
{
    global $max_sticks;
    global $pyramid_rows;

    $removed_sticks = mt_rand(1, $max_sticks);
    $on_row = mt_rand(1, $pyramid_rows);
    for ($i = 1; $i <= $pyramid_rows; $i++) {
        if ($game_table[$i] > $game_table[$on_row]) {
            $on_row = array_search($game_table[$i], $game_table);
        }
    }
    //AI select only EVEN NUMBERS
    while ($removed_sticks % 2 !== 0) {
        print("REMOVE AN EVEN NUMBER NOOB: $removed_sticks" . PHP_EOL);
        $removed_sticks = mt_rand(1, $max_sticks);
    }
    if (array_sum($game_table) < ($max_sticks) && (array_sum($game_table) - $max_sticks) % 2 === 1) {
        $removed_sticks = $max_sticks - 1;
    }
    $count = 0;
    for ($i = 1; $i < $pyramid_rows; $i++) {
        if ($game_table[$i] === 1) {
            $count++;
        }
        if ($count >= 2 && $count % 2 === 1) {
            $on_row = max($game_table);
            $removed_sticks = $game_table[$on_row] + 1;
        }
    }
    if (array_sum($game_table) % 2 === 1 && array_sum($game_table) < $max_sticks) {
        $removed_sticks = $game_table[$on_row] + 1;
    }
    if ($pyramid_rows % 2 === 0 && array_sum($game_table) <= $max_sticks) {
        while ($removed_sticks % 2 === 0) {
            print('REMOVE ODD NUMBER NOOB: ' . $removed_sticks . PHP_EOL);
            $removed_sticks = mt_rand(1, $max_sticks);
        }
    }
    if ($pyramid_rows % 2 !== 0 && array_sum($game_table) <= $max_sticks) {
        while ($removed_sticks % 2 === 0) {
            print('REMOVE EVEN NUMBER NOOB: ' . $removed_sticks . PHP_EOL);
            $removed_sticks = mt_rand(1, $max_sticks);
        }
    }
    for ($i = 1; $i < $pyramid_rows; $i++) {
        $count = 0;
        if ($game_table[$i] !== 0) {
            $count++;
        }
        if ($count === 3) {
            $removed_sticks = $max_sticks;
            $on_row = max($game_table);
        }
    }
    print("\033[32m Computer removed $removed_sticks stick(s) from line $on_row \033[0m" . PHP_EOL);
    updatePyramidAI($game_table, $on_row, $removed_sticks);
    print PHP_EOL;
}

//player's turn
function playersTurn(&$game_table)
{
    global $max_sticks;
    global $pyramid_rows;
    print('How many sticks would you like to remove?' . PHP_EOL);
    $removed_sticks = trim(fgets(STDIN));
    $removed_sticks = checkInputSticks($removed_sticks, $max_sticks);
    print('On which row that might be?' . PHP_EOL);
    $on_row = trim(fgets(STDIN));
    $on_row = checkInputRow($on_row, $pyramid_rows);
    print("\033[31m Player removed $removed_sticks stick(s) from line $on_row \033[0m" . PHP_EOL);
    updatePyramidPlayer($game_table, $pyramid_rows, $on_row, $removed_sticks);
    print PHP_EOL;
}


function printPyramid(&$game_table)
{
    $last = (count($game_table) * 2) - 1;
    foreach ($game_table as $index => $row) {

        $space = ($last - ($index * 2 - 1)) / 2;
        echo '*';
        $i = 0;
        // draw space before stick
        while ($i++ < $space) {
            echo '  ';
        }
        //draw stick
        $j = 0;
        while ($j++ < $row) {
            echo '| ';
        }
        // draw space stick remove
        $k = 0;
        $space_remove = ($last - $row) - ($space * 2);
        while ($k++ < $space_remove) {
            echo '  ';
        }
        $i = 0;
        // draw space before stick
        while ($i++ < $space) {
            echo '  ';
        }
        echo '*' . PHP_EOL;
    }
    return $game_table;
}

function updatePyramidPlayer(&$game_table, $pyramid_rows, $on_row, $removed_sticks)
{
    while (!ctype_digit($on_row) || $on_row < 0 || $on_row > $pyramid_rows || $game_table[$on_row] === 0) {
        print('Pick a NON NULL row: ');
        $on_row = trim(fgets(STDIN));
    }
    $game_table[$on_row] -= $removed_sticks;
    //stopping decrementing rows into negative numbers
    $game_table[$on_row] = $game_table[$on_row] < 0 ? 0 : $game_table[$on_row];
    printPyramid($game_table);
    return $game_table;
}

function updatePyramidAI(&$game_table, $on_row, $removed_sticks)
{
    global $pyramid_rows;
    global $star_border;
    while ($game_table[$on_row] <= 0) {
        $on_row = mt_rand(1, $pyramid_rows);
    }
    print($star_border . PHP_EOL);
    $game_table[$on_row] -= $removed_sticks;
    //stopping decrementing rows into negative numbers
    $game_table[$on_row] = ($game_table[$on_row] < 0) ? 0 : $game_table[$on_row];
    printPyramid($game_table);
    return $game_table;
}

function CreatePyramid($pyramid_rows, $game_table)
{
    $total_rows = $pyramid_rows;
    for ($row = 1; $row <= $pyramid_rows; $row++) { // Loop to print rows
        for ($space_counter = 1; $space_counter < $total_rows * 2; $space_counter++) { // Loop to print spaces in a row
            print' ';
        }
        $total_rows--;
        for ($stick = 1; $stick <= 2 * $row - 1; $stick++) { // Loop to print stars in a row
            print'| ';
        }
        print PHP_EOL;
        $game_table[$row] = $stick - 1;
    }
    return $game_table;
}

//check stick input
function checkInputSticks($removed_sticks, $max_sticks)
{
    while ($removed_sticks < 0 || !is_numeric($removed_sticks) || empty($removed_sticks) || ($removed_sticks > $max_sticks)) {
        print('Please select the correct type of input (int) and size' . PHP_EOL);
        print('How many sticks would you like to remove? ' . PHP_EOL);
        $removed_sticks = trim(fgets(STDIN));
    }
    return $removed_sticks;
}

//check row input
function checkInputRow($on_row, $pyramid_rows)
{
    while ($on_row < 0 || !is_numeric($on_row) || empty($on_row) || ($on_row > $pyramid_rows)) {
        print('Please select the correct type of input (int) and size' . PHP_EOL);
        print('On which row that might be? ' . PHP_EOL);
        $on_row = trim(fgets(STDIN));
    }
    return $on_row;
}

//check arguments
function checkArguments($arguments)
{
    if (empty($arguments) || count($arguments) !== 2 || !(is_numeric($arguments[0]) && is_numeric($arguments[1])) || !($arguments[0] > 0 && $arguments[1] > 0)) {
        print('Please set (int):remove max sticks && (int):max rows between 2~99' . PHP_EOL);
        die();
    }
}

function checkWin(&$game_table)
{
    global $turn;
    $sum = array_sum($game_table);
    if ($sum === 0) {
        print ($turn === 0 ? 'PLAYER' : 'COMPUTER') . ' WON!' . PHP_EOL;
        die();
    }
    if ($sum === 1) {
        print ($turn === 1 ? 'COMPUTER' : 'PLAYER') . ' WON !' . PHP_EOL;
        die();
    }
}
