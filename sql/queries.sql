# recent flips

SELECT DISTINCT us.username, fl.time_flipped, fa.name as face_name
FROM flip fl
JOIN user us
ON fl.user_id = us.id
JOIN face fa
ON fl.face_id = fa.id
WHERE username = ?
ORDER BY time_flipped DESC
LIMIT 10

# recent streaks

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
ORDER BY length DESC
LIMIT 10
