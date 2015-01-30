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
 ORDER BY time_flipped DESC, microseconds DESC
 LIMIT 10

# recent flips

SELECT us.username, fa.name as face_name
FROM flip fl
JOIN user us
ON us.id = fl.user_id
JOIN face fa
ON fa.id = fl.face_id
WHERE username = ?
ORDER BY time_flipped DESC, microseconds DESC
LIMIT 10
