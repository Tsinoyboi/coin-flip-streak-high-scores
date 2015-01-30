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
ORDER BY fl.time_flipped DESC, fl.microseconds
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
ORDER BY fl.time_flipped DESC, fl.microseconds
LIMIT 10

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
