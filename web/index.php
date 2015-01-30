<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();

$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

$app->register(new Silex\Provider\SessionServiceProvider());

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__ . '/../config/parameters.yml'));

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => $app['parameters'],
));

$app->get('/', function () use ($app) {
    $user = $app['session']->get('user');
    return $app['twig']->render('front.twig', array(
    'sessionuser' => $user['username'],
    ));
})->bind('front');

$app->get('/signin', function () use ($app) {
    $user = $app['session']->get('user');
    return $app['twig']->render('signin.twig', array(
        'sessionuser' => $user['username'],
        'isValid' => true,
    ));
});

$app->post('/authenticate', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');
    $sql = "SELECT hash FROM user WHERE username = ?";
    $prepared = array(
        $username,
    );
    $userResult = $app['db']->fetchAssoc($sql, $prepared);

    if (false === password_verify($password, $userResult['hash'])) {
        $user = $app['session']->get('user');
        return $app['twig']->render('signin.twig', array(
            'username' => $username,
            'sessionuser' => $user['username'],
            'isValid' => false,
        ));
    }
    $app['session']->set('user', array('username' => $username));
    return $app->redirect('./flip');
});

$app->post('/registrate', function (Request $request) use ($app) {
    $user = $app['session']->get('user');
    $username = $request->get('username');
    $password = $request->get('password');
    $confirm = $request->get('confirm');
    if ($password !== $confirm) {
        return $app['twig']->render('register.twig', array(
            'username' => $username,
            'sessionuser' => $user['username'],
            'isConfirmed' => false,
            'userExists' => false,
        ));
    }
    $sql = "SELECT username FROM user WHERE username = ?";
    $prepared = array(
        $username,
    );
    $userResult = $app['db']->fetchAssoc($sql, $prepared);
    if (false === $userResult) {
        $sql = "INSERT INTO user (username, hash) VALUES (?, ?)";
        $prepared = array(
            $username,
            password_hash($password, PASSWORD_BCRYPT, array('cost' => 12)),
        );
        $app['db']->executeUpdate($sql, $prepared);
        // display sign in with username filled
        return $app['twig']->render('signin.twig', array(
            'username' => $username,
            'sessionuser' => $user['username'],
            'isValid' => true,
        ));
    } else {
        // display register with user exists error
        return $app['twig']->render('register.twig', array(
            'username' => $username,
            'sessionuser' => $user['username'],
            'isConfirmed' => true,
            'userExists' => true,
        ));
    }
});

$app->get('/register', function () use ($app) {
    $user = $app['session']->get('user');
    return $app['twig']->render('register.twig', array(
        'sessionuser' => $user['username'],
        'isConfirmed' => true,
        'userExists' => false,
    ));
});

$app->post('/passwordchangiate', function (Request $request) use ($app) {
    $user = $app['session']->get('user');
    $oldPassword = $request->get('oldPassword');
    $newPassword = $request->get('newPassword');
    $confirm = $request->get('confirm');

    // confirm idential new passwords
    if ($newPassword !== $confirm) {
        return $app['twig']->render('changepassword.twig', array(
            'username' => $username,
            'sessionuser' => $user['username'],
            'isConfirmed' => false,
            'isValid' => true,
        ));
    }

    // compare old password to database
    $sql = "SELECT hash FROM user WHERE username = ?";
    $prepared = array(
        $user['username'],
    );
    $userResult = $app['db']->fetchAssoc($sql, $prepared);

    if (false === password_verify($oldPassword, $userResult['hash'])) {
        // if no match, then render page with error
        return $app['twig']->render('changepassword.twig', array(
            'username' => $username,
            'sessionuser' => $user['username'],
            'isConfirmed' => true,
            'isValid' => false,
        ));
    } else {
        // if match, then update database and return to settings
        $sql = "UPDATE user";
        $sql .= " SET hash = ?";
        $sql .= " WHERE username = ?";
        $prepared = array(
            password_hash($newPassword, PASSWORD_BCRYPT, array('cost' => 12)),
            $user['username'],
        );
        $app['db']->executeUpdate($sql, $prepared);
        return $app->redirect('./settings');
        // no confirmation message?
    }
});

$app->get('/changepassword', function () use ($app) {
    $user = $app['session']->get('user');
    if ((null === $user) || (false === $user)) {
        return $app->redirect('./signin');
    }
    return $app['twig']->render('changepassword.twig', array(
        'sessionuser' => $user['username'],
        'isConfirmed' => true,
        'isValid' => true,
    ));
});

$app->get('/settings', function () use ($app) {
    $user = $app['session']->get('user');
    if ((null === $user) || (false === $user)) {
        return $app->redirect('./signin');
    }
    return $app['twig']->render('settings.twig', array(
        'sessionuser' => $user['username'],
    ));
});

$app->get('/highscores', function () use ($app) {
    $user = $app['session']->get('user');

    $sql = "SELECT us.username, count(*) as length, fa.name as face_name";
    $sql .= " FROM flip fl";
    $sql .= " JOIN streak st";
    $sql .= " ON st.id = fl.streak_id";
    $sql .= " JOIN user us";
    $sql .= " ON us.id = st.user_id";
    $sql .= " JOIN face fa";
    $sql .= " ON fa.id = st.face_id";
    $sql .= " GROUP BY st.id";
    $sql .= " ORDER BY length DESC, milliseconds DESC";
    $sql .= " LIMIT 10";

    $prepared = array(
    );

    $highResult = $app['db']->fetchAll($sql, $prepared);

    return $app['twig']->render('highscores.twig', array(
        'sessionuser' => $user['username'],
        'highresult' => $highResult,
    ));
});

$app->get('/profile', function () use ($app) {
    $user = $app['session']->get('user');
    if ((null === $user) || (false === $user)) {
        return $app->redirect('./signin');
    }

    $sql = "SELECT fl.time_flipped, fl.milliseconds, fa.name as face_name";
    $sql .= " FROM flip fl";
    $sql .= " JOIN streak st";
    $sql .= " ON fl.streak_id = st.id";
    $sql .= " JOIN user us";
    $sql .= " ON st.user_id = us.id";
    $sql .= " JOIN face fa";
    $sql .= " ON st.face_id = fa.id";
    $sql .= " WHERE username = ?";
    $sql .= " ORDER BY fl.time_flipped DESC, fl.milliseconds DESC";
    $sql .= " LIMIT 10";

    $prepared = array(
        $user['username']
    );

    $recentFlipResult = $app['db']->fetchAll($sql, $prepared);

    $sql = "SELECT fl.time_flipped, fl.milliseconds, count(*) as length, fa.name as face_name";
    $sql .= " FROM flip fl";
    $sql .= " JOIN streak st";
    $sql .= " ON fl.streak_id = st.id";
    $sql .= " JOIN user us";
    $sql .= " ON st.user_id = us.id";
    $sql .= " JOIN face fa";
    $sql .= " ON st.face_id = fa.id";
    $sql .= " WHERE us.username = ?";
    $sql .= " GROUP BY st.id";
    $sql .= " ORDER BY fl.time_flipped DESC, fl.milliseconds DESC";
    $sql .= " LIMIT 10";

    $prepared = array(
        $user['username']
    );

    $recentStreakResult = $app['db']->fetchAll($sql, $prepared);

    $sql = "SELECT us.username, count(*) as length, fa.name as face_name";
    $sql .= " FROM flip fl";
    $sql .= " JOIN streak st";
    $sql .= " ON st.id = fl.streak_id";
    $sql .= " JOIN user us";
    $sql .= " ON us.id = st.user_id";
    $sql .= " JOIN face fa";
    $sql .= " ON fa.id = st.face_id";
    $sql .= " WHERE us.username = ?";
    $sql .= " GROUP BY st.id";
    $sql .= " ORDER BY length DESC";
    $sql .= " LIMIT 10";

    $prepared = array(
        $user['username']
    );

    $bestStreakResult = $app['db']->fetchAll($sql, $prepared);

    return $app['twig']->render('profile.twig', array(
        'sessionuser' => $user['username'],
        'recentFlipResult' => $recentFlipResult,
        'recentStreakResult' => $recentStreakResult,
        'bestStreakResult' => $bestStreakResult,
    ));
});

$app->get('/flip', function () use ($app) {
    $user = $app['session']->get('user');
    if ((null === $user) || (false === $user)) {
        return $app->redirect('./signin');
    }
    $sql = "SELECT COUNT(*) AS length, fa.name AS face_name";
    $sql .= " FROM";
    $sql .= " (";
    $sql .= "  SELECT st.id";
    $sql .= "   FROM user us";
    $sql .= "   LEFT JOIN streak st";
    $sql .= "   ON st.user_id = us.id";
    $sql .= "   LEFT JOIN flip fl";
    $sql .= "   ON fl.streak_id = st.id";
    $sql .= "   WHERE us.username = ?";
    $sql .= "   ORDER BY fl.time_flipped DESC, fl.milliseconds DESC";
    $sql .= "   LIMIT 1";
    $sql .= " ) stid";
    $sql .= " JOIN streak st";
    $sql .= " ON st.id = stid.id";
    $sql .= " JOIN face fa";
    $sql .= " ON fa.id = st.face_id";
    $sql .= " JOIN flip fl";
    $sql .= " ON fl.streak_id = st.id";
    $sql .= " GROUP BY fa.name";

    $prepared = array(
        $user['username']
    );

    $result = $app['db']->fetchAssoc($sql, $prepared);

    return $app['twig']->render('flip.twig', array(
        'sessionuser' => $user['username'],
        'lastflip' => $result['face_name'],
        'streaklength' => $result['length'],
    ));
});

$app->post('/flippate', function() use($app) {
    $user = $app['session']->get('user');
    if ((null === $user) || (false === $user)) {
        return $app->redirect('./signin');
    }
    $coin = mt_rand(0, 1);
    if (0 === $coin) {
        $faceName = 'tails';
    } else {
        $faceName = 'heads';
    }
    $sql = "SELECT id FROM face WHERE name = ?";
    $prepared = array(
        $faceName,
    );
    $newFaceResult = $app['db']->fetchAssoc($sql, $prepared);
        $user = $app['session']->get('user');

    $sql = "SELECT id FROM user WHERE username = ?";

    $prepared = array(
        $user['username'],
    );
    $userResult = $app['db']->fetchAssoc($sql, $prepared);

    $sql = "SELECT fl.streak_id, st.face_id";
    $sql .= " FROM flip fl";
    $sql .= " JOIN streak st";
    $sql .= " ON fl.streak_id = st.id";
    $sql .= " WHERE st.user_id = ?";
    $sql .= " ORDER BY fl.time_flipped DESC,  fl.milliseconds DESC";
    $sql .= " LIMIT 1";

    $prepared = array(
        $userResult['id'],
    );
    $result = $app['db']->fetchAssoc($sql, $prepared);

    if ((false === $result) || ($result['face_id'] !== $newFaceResult['id'])) {
        // if no match or previous streak is other face, then start a new streak
        $sql = "INSERT INTO streak (user_id, face_id) VALUES (?, ?)";
        $prepared = array(
            $userResult['id'],
            $newFaceResult['id'],
        );
        $app['db']->executeUpdate($sql, $prepared);
        $result['streak_id'] = $app['db']->lastInsertId();
    }

    // new flip and attach to streak
    $sql = "INSERT INTO flip (streak_id, time_flipped, milliseconds) VALUES (?, ?, ?)";
    $now = new \DateTime();
    $prepared = array(
        $result['streak_id'],
        $now->format('Y-m-d H:i:s'),
        (microtime(true) - time(true)) * 1000,
    );
    $app['db']->executeUpdate($sql, $prepared);

    return $app->redirect('./flip');
});

$app->get('/signout', function () use ($app) {
    $app['session']->set('user', false);
    return $app->redirect('./');
});

$app->run();
