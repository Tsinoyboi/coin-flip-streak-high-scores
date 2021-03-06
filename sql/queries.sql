# recent flips

SELECT fl.time_flipped, fa.name as face_name
FROM flip fl
JOIN user us
ON fl.user_id = us.id
JOIN face fa
ON fl.face_id = fa.id
WHERE username = ?
ORDER BY fl.time_flipped DESC
LIMIT 10


-- new recent flips SELECT fl.time_flipped, fa.name as face_name
SELECT fl.time_flipped, fa.name as face_name
FROM flip fl
JOIN streak st
ON fl.streak_id = st.id
JOIN user us
ON st.user_id = us.id
JOIN face fa
ON st.face_id = fa.id
WHERE username = 'b'
ORDER BY fl.time_flipped DESC, fl.milliseconds
LIMIT 10

# recent streaks

SELECT DISTINCT st.id, fl.time_flipped, us.username, st.length, fa.name as face_name
FROM flip fl
JOIN streak st
ON fl.streak_id = st.id
JOIN user us
ON fl.user_id = us.id
JOIN face fa
ON fl.face_id = fa.id
WHERE us.username = 'john'
GROUP BY st.id
ORDER BY fl.length DESC
LIMIT 10

-- new recent streaks

SELECT fl.time_flipped, count(*) as length, fa.name as face_name
FROM flip fl
JOIN streak st
ON fl.streak_id = st.id
JOIN user us
ON st.user_id = us.id
JOIN face fa
ON st.face_id = fa.id
WHERE us.username = 'b'
GROUP BY st.id
ORDER BY fl.time_flipped DESC, fl.milliseconds
LIMIT 10

# user last flip

SELECT  us.username, fa.name as facename, fl.time_flipped
FROM `flip` fl
JOIN streak st
ON fl.streak_id = st.id
JOIN user us
ON st.user_id = us.id
JOIN face fa
ON st.face_id = fa.id
WHERE us.username = "a"
ORDER BY fl.time_flipped DESC
LIMIT 1

-- user last flip name and count

SELECT COUNT(*) AS length, fa.name AS face_name
FROM
(
    SELECT st.id
    FROM user us
    LEFT JOIN streak st
    ON st.user_id = us.id
    LEFT JOIN flip fl
    ON fl.streak_id = st.id
    WHERE us.username = 'john'
    ORDER BY fl.time_flipped DESC, fl.milliseconds
    LIMIT 1
) stid
JOIN streak st
ON st.id = stid.id
JOIN face fa
ON fa.id = st.face_id
JOIN flip fl
ON fl.streak_id = st.id
GROUP BY fa.name


-- old high scores

SELECT DISTINCT us.username, st.length, fa.name AS face_name
 FROM streak st
 JOIN user us
 ON st.user_id = us.id
 JOIN face fa
 ON st.face_id = fa.id
 ORDER BY length DESC
 LIMIT 10

-- new high scores

SELECT us.username, count(*) as length, fa.name as face_name
 FROM flip fl
 JOIN streak st
 ON st.id = fl.streak_id
 JOIN user us
 ON us.id = st.user_id
 JOIN face fa
 ON fa.id = st.face_id
 GROUP BY st.id
 ORDER BY length DESC
 LIMIT 10
