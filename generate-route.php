<?php
    session_start();
    require "connect.php";

    $routes_output = exec("python ./RouteAssign.py");
    $routes_by_trucks = json_decode($routes_output, true);

    // Ensure that the JSON structure is as expected
    if (!is_array($routes_by_trucks) || !isset($routes_by_trucks[0]) || !isset($routes_by_trucks[1])) {
        echo "Unexpected JSON structure.";
        exit;
    }
    $schedule_id_all = $routes_by_trucks[1];
    // echo count($schedule_id_all);
    $routes = $routes_by_trucks[0];
    $count = 0;

    foreach ($schedule_id_all as $schedule_id) {
        if (is_array($routes[$count])){
            $route_for_this_schedule = $routes[$count];
            foreach ($route_for_this_schedule as $location) {
                $sql_insert_route = "INSERT INTO `route` (`id_schedule`, `id_location`) VALUES (?, ?)";
                $stmt_insert = mysqli_prepare($con, $sql_insert_route);
                mysqli_stmt_bind_param($stmt_insert, "ii", $schedule_id, $location);
                mysqli_stmt_execute($stmt_insert);
                // echo "Schedule ID: ".$schedule_id;
                // echo "<br>";
                // echo "Location ID: ".$location;
                // echo "<br>";

            }
        }
        // echo "<br>";
        $count++;
    }


?>