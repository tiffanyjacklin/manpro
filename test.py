UPDATE `truck`
SET `id_location` = (
    SELECT `id_location_from`
    FROM `schedule`
    WHERE `id_truk` = 1 AND `status` = 1
    ORDER BY `id_schedule`
    LIMIT 1
)
WHERE id = 1;

UPDATE `truck`
SET `id_location` = (
    SELECT `id_location_from`
    FROM `schedule`
    WHERE `id_truk` = 2 AND `status` = 1
    ORDER BY `id_schedule`
    LIMIT 1
)
WHERE id = 2;

UPDATE `truck`
SET `id_location` = (
    SELECT `id_location_from`
    FROM `schedule`
    WHERE `id_truk` = 3 AND `status` = 1
    ORDER BY `id_schedule`
    LIMIT 1
)
WHERE id = 3;

UPDATE `truck`
SET `id_location` = (
    SELECT `id_location_from`
    FROM `schedule`
    WHERE `id_truk` = 4 AND `status` = 1
    ORDER BY `id_schedule`
    LIMIT 1
)
WHERE id = 4;

UPDATE `truck`
SET `id_location` = (
    SELECT `id_location_from`
    FROM `schedule`
    WHERE `id_truk` = 5 AND `status` = 1
    ORDER BY `id_schedule`
    LIMIT 1
)
WHERE id = 5;

UPDATE `truck`
SET `id_location` = (
    SELECT `id_location_from`
    FROM `schedule`
    WHERE `id_truk` = 6 AND `status` = 1
    ORDER BY `id_schedule`
    LIMIT 1
)
WHERE id = 6;

UPDATE `truck`
SET `id_location` = (
    SELECT `id_location_from`
    FROM `schedule`
    WHERE `id_truk` = 7 AND `status` = 1
    ORDER BY `id_schedule`
    LIMIT 1
)
WHERE id = 7;

UPDATE `truck`
SET `id_location` = (
    SELECT `id_location_from`
    FROM `schedule`
    WHERE `id_truk` = 8 AND `status` = 1
    ORDER BY `id_schedule`
    LIMIT 1
)
WHERE id = 8;

UPDATE `truck`
SET `id_location` = (
    SELECT `id_location_from`
    FROM `schedule`
    WHERE `id_truk` = 9 AND `status` = 1
    ORDER BY `id_schedule`
    LIMIT 1
)
WHERE id = 9;

UPDATE `truck`
SET `id_location` = (
    SELECT `id_location_from`
    FROM `schedule`
    WHERE `id_truk` = 10 AND `status` = 1
    ORDER BY `id_schedule`
    LIMIT 1
)
WHERE id = 10;

UPDATE `truck`
SET `id_location` = (
    SELECT `id_location_from`
    FROM `schedule`
    WHERE `id_truk` = 11 AND `status` = 1
    ORDER BY `id_schedule`
    LIMIT 1
)
WHERE id = 11;

UPDATE `truck`
SET `id_location` = (
    SELECT `id_location_from`
    FROM `schedule`
    WHERE `id_truk` = 12 AND `status` = 1
    ORDER BY `id_schedule`
    LIMIT 1
)
WHERE id = 12;

UPDATE `truck`
SET `id_location` = (
    SELECT `id_location_from`
    FROM `schedule`
    WHERE `id_truk` = 13 AND `status` = 1
    ORDER BY `id_schedule`
    LIMIT 1
)
WHERE id = 13;

UPDATE `truck`
SET `id_location` = (
    SELECT `id_location_from`
    FROM `schedule`
    WHERE `id_truk` = 14 AND `status` = 1
    ORDER BY `id_schedule`
    LIMIT 1
)
WHERE id = 14;

UPDATE `truck`
SET `id_location` = (
    SELECT `id_location_from`
    FROM `schedule`
    WHERE `id_truk` = 15 AND `status` = 1
    ORDER BY `id_schedule`
    LIMIT 1
)
WHERE id = 15;

UPDATE `truck`
SET `id_location` = (
    SELECT `id_location_from`
    FROM `schedule`
    WHERE `id_truk` = 16 AND `status` = 1
    ORDER BY `id_schedule`
    LIMIT 1
)
WHERE id = 16;
