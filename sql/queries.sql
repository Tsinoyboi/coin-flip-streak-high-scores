# user last flip

SELECT fa.name as face_name
FROM user us
LEFT JOIN flip fl
ON us.id = fl.user_id
JOIN face fa
ON fl.face_id = fa.id
WHERE us.username = 'john'
ORDER BY fl.time_flipped DESC, fl.microseconds
LIMIT 1

-- new high scores

SELECT us.username, fa.name as face_name
 FROM flip fl
 JOIN user us
 ON us.id = fl.user_id
 JOIN face fa
 ON fa.id = fl.face_id
 ORDER BY fl.time_flipped DESC, fl.microseconds DESC
 LIMIT 10

# recent flips

SELECT us.username, fa.name as face_name
FROM flip fl
JOIN user us
ON us.id = fl.user_id
JOIN face fa
ON fa.id = fl.face_id
WHERE us.username = ?
ORDER BY fl.time_flipped DESC, fl.microseconds DESC
LIMIT 10

SELECT fli.time_flipped, fli.microseconds, fli.face_id,
(SELECT COUNT(*)
FROM flip fl
WHERE fl.face_id <> fli.face_id
AND fl.time_flipped <= fli.time_flipped) as RunGroup
FROM flip fli
ORDER BY fli.time_flipped DESC, fli.microseconds DESC

SELECT fli.user_id, fli.super_time, fli.face_id,
(SELECT COUNT(*)
FROM (SELECT user_id, face_id, (UNIX_TIMESTAMP(time_flipped) * 1000000) + microseconds AS super_time
FROM `flip`) fl
WHERE fl.face_id <> fli.face_id
AND fl.super_time <= fli.super_time) as RunGroup
FROM (SELECT user_id, face_id, (UNIX_TIMESTAMP(time_flipped) * 1000000) + microseconds AS super_time
FROM `flip`) fli
ORDER BY fli.super_time DESC

SELECT user_id, face_id, MAX((UNIX_TIMESTAMP(time_flipped) * 1000000) + microseconds) AS super_time
FROM `flip`
WHERE 1
GROUP BY user_id, face_id

SELECT user_id, face_id, (UNIX_TIMESTAMP(time_flipped) * 1000000) + microseconds AS super_time
FROM `flip`

SELECT
SUM(
    CASE
    WHEN face.name = 'Heads' THEN 1 ELSE 0 END
) as Heads,
SUM(
    CASE
    WHEN face.name = 'Tails' THEN 1 ELSE 0 END
) as Tails
FROM flip
JOIN face
ON face.id = flip.face_id

SELECT Result, MIN(GameDate) as StartDate, MAX(GameDate) as EndDate, COUNT(*) as Games
FROM
(
    SELECT fli.time_flipped, fli.microseconds, fli.face_id,
    (SELECT COUNT(*)
    FROM flip fl
    WHERE fl.face_id <> fli.face_id
    AND fl.time_flipped <= fli.time_flipped) as RunGroup
    FROM flip fli
    ORDER BY fli.time_flipped DESC, fli.microseconds DESC
) A GROUP BY Result, RunGroup ORDER BY Min(GameDate)


SELECT user_id, face_id, MIN(super_time) as start_time, MAX(super_time) as end_time, COUNT(*) as flips FROM (

    SELECT fli.user_id, fli.super_time, fli.face_id,
    (SELECT COUNT(*)
    FROM (SELECT user_id, face_id, (UNIX_TIMESTAMP(time_flipped) * 1000000) + microseconds AS super_time
    FROM `flip`) fl
    WHERE fl.face_id <> fli.face_id
    AND fl.super_time <= fli.super_time) as RunGroup
    FROM (SELECT user_id, face_id, (UNIX_TIMESTAMP(time_flipped) * 1000000) + microseconds AS super_time
    FROM `flip`) fli

) A GROUP BY face_id, RunGroup ORDER BY MAX(super_time) DESC

SELECT user_id, face_id, start_time, MOD(start_time, 1000000) as start_us, end_time, MOD(end_time, 1000000) as end_us, length fROM

(SELECT user_id, face_id, MIN(super_time) as start_time, MAX(super_time) as end_time, COUNT(*) as length FROM (

    SELECT fli.user_id, fli.super_time, fli.face_id,
    (SELECT COUNT(*)
    FROM (SELECT user_id, face_id, (UNIX_TIMESTAMP(time_flipped) * 1000000) + microseconds AS super_time
    FROM `flip`) fl
    WHERE fl.face_id <> fli.face_id
    AND fl.super_time <= fli.super_time) as RunGroup
    FROM (SELECT user_id, face_id, (UNIX_TIMESTAMP(time_flipped) * 1000000) + microseconds AS super_time
    FROM `flip`) fli

) A GROUP BY face_id, RunGroup ORDER BY MAX(super_time) DESC) B

SELECT user_id, face_id, FROM_UNIXTIME(FLOOR(start_time / 1000000)) as start_tf, MOD(start_time, 1000000) as start_us, FROM_UNIXTIME(FLOOR(end_time / 1000000)) as end_tf, MOD(end_time, 1000000) as end_us, length fROM

(SELECT user_id, face_id, MIN(super_time) as start_time, MAX(super_time) as end_time, COUNT(*) as length FROM (

    SELECT fli.user_id, fli.super_time, fli.face_id,
    (SELECT COUNT(*)
    FROM (SELECT user_id, face_id, (UNIX_TIMESTAMP(time_flipped) * 1000000) + microseconds AS super_time
    FROM `flip`) fl
    WHERE fl.face_id <> fli.face_id
    AND fl.super_time <= fli.super_time) as RunGroup
    FROM (SELECT user_id, face_id, (UNIX_TIMESTAMP(time_flipped) * 1000000) + microseconds AS super_time
    FROM `flip`) fli

) A GROUP BY face_id, RunGroup ORDER BY MAX(super_time) DESC) B


SELECT user.username, face.name as face_name, FROM_UNIXTIME(FLOOR(start_time / 1000000)) as start_tf, MOD(start_time, 1000000) as start_us, FROM_UNIXTIME(FLOOR(end_time / 1000000)) as end_tf, MOD(end_time, 1000000) as end_us, length fROM

(SELECT user_id, face_id, MIN(super_time) as start_time, MAX(super_time) as end_time, COUNT(*) as length FROM (

    SELECT fli.user_id, fli.super_time, fli.face_id,
    (SELECT COUNT(*)
    FROM (SELECT user_id, face_id, (UNIX_TIMESTAMP(time_flipped) * 1000000) + microseconds AS super_time
    FROM `flip`) fl
    WHERE fl.face_id <> fli.face_id
    AND fl.super_time <= fli.super_time) as RunGroup
    FROM (SELECT user_id, face_id, (UNIX_TIMESTAMP(time_flipped) * 1000000) + microseconds AS super_time
    FROM `flip`) fli

) A GROUP BY face_id, RunGroup) A
JOIN face
ON face.id = A.face_id
JOIN user
ON user.id = A.user_id
ORDER BY end_time DESC


SELECT user.username,
 face.name AS face_name,
 FROM_UNIXTIME(FLOOR(start_time / 1000000)) AS start_tf,
 MOD(start_time, 1000000) AS start_us,
 FROM_UNIXTIME(FLOOR(end_time / 1000000)) AS end_tf,
 MOD(end_time, 1000000) as end_us, length
 FROM (
     SELECT user_id,
      face_id,
      MIN(super_time) AS start_time,
      MAX(super_time) AS end_time,
      COUNT(*) AS length
      FROM (
          SELECT fli.user_id,
          fli.super_time,
          fli.face_id, (
              SELECT COUNT(*)
              FROM (
                  SELECT user_id,
                  face_id,
                  (UNIX_TIMESTAMP(time_flipped) * 1000000) + microseconds AS super_time
    FROM `flip`) fl
    WHERE fl.face_id <> fli.face_id
    AND fl.super_time <= fli.super_time) AS RunGroup
    FROM (
        SELECT user_id,
        face_id,
        (UNIX_TIMESTAMP(time_flipped) * 1000000) + microseconds AS super_time
    FROM `flip`) fli

) A GROUP BY face_id, RunGroup) A
JOIN face
ON face.id = A.face_id
JOIN user
ON user.id = A.user_id
ORDER BY end_time DESC

SELECT user.username, face.name AS face_name, time_flipped, microseconds
FROM `flip`
JOIN user
ON user.id = flip.user_id
JOIN face
ON face.id = flip.face_id
