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

    $sql = "SELECT us.username, fa.name as face_name";
    $sql .= " FROM flip fl";
    $sql .= " JOIN user us";
    $sql .= " ON us.id = fl.user_id";
    $sql .= " JOIN face fa";
    $sql .= " ON fa.id = fl.face_id";
    $sql .= " ORDER BY time_flipped DESC, microseconds DESC";
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

    $sql = "SELECT fl.time_flipped, fl.microseconds, fa.name as face_name";
    $sql .= " FROM flip fl";
    $sql .= " JOIN user us";
    $sql .= " ON fl.user_id = us.id";
    $sql .= " JOIN face fa";
    $sql .= " ON fl.face_id = fa.id";
    $sql .= " WHERE username = ?";
    $sql .= " ORDER BY fl.time_flipped DESC, fl.microseconds DESC";
    $sql .= " LIMIT 10";

    $prepared = array(
        $user['username']
    );

    $recentFlipResult = $app['db']->fetchAll($sql, $prepared);

    $sql = "SELECT fl.time_flipped, fl.microseconds, fa.name as face_name";
    $sql .= " FROM flip fl";
    $sql .= " JOIN user us";
    $sql .= " ON fl.user_id = us.id";
    $sql .= " JOIN face fa";
    $sql .= " ON fl.face_id = fa.id";
    $sql .= " WHERE username = ?";
    $sql .= " ORDER BY fl.time_flipped DESC, fl.microseconds DESC";
    $sql .= " LIMIT 10";

    $prepared = array(
        $user['username']
    );

    $recentStreakResult = $app['db']->fetchAll($sql, $prepared);

    $sql = "SELECT fa.name as face_name";
    $sql .= " FROM flip fl";
    $sql .= " JOIN user us";
    $sql .= " ON fl.user_id = us.id";
    $sql .= " JOIN face fa";
    $sql .= " ON fl.face_id = fa.id";
    $sql .= " WHERE username = ?";
    $sql .= " ORDER BY fl.time_flipped DESC, fl.microseconds DESC";
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
    $sql = "SELECT fa.name as face_name";
    $sql .= " FROM user us";
    $sql .= " LEFT JOIN flip fl";
    $sql .= " ON us.id = fl.user_id";
    $sql .= " JOIN face fa";
    $sql .= " ON fl.face_id = fa.id";
    $sql .= " WHERE us.username = ?";
    $sql .= " ORDER BY fl.time_flipped DESC, fl.microseconds";
    $sql .= " LIMIT 1";

    $prepared = array(
        $user['username']
    );

    $result = $app['db']->fetchAssoc($sql, $prepared);

    return $app['twig']->render('flip.twig', array(
        'sessionuser' => $user['username'],
        'lastflip' => $result['face_name'],
        'streaklength' => "",
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

    $sql = "SELECT id FROM user WHERE username = ?";

    $prepared = array(
        $user['username'],
    );
    $userResult = $app['db']->fetchAssoc($sql, $prepared);

    // store new flip
    $sql = "INSERT INTO flip (user_id, face_id, time_flipped, microseconds) VALUES (?, ?, ?, ?)";

    // record times
    $now = new \DateTime();
    $micro_time = microtime(true);
    $floored = floor($micro_time);
    $microseconds = round(($micro_time - $floored) * 1000000);
    //
    $prepared = array(
        $userResult['id'],
        $newFaceResult['id'],
        $now->format('Y-m-d H:i:s'),
        $microseconds,
    );
    $app['db']->executeUpdate($sql, $prepared);

    return $app->redirect('./flip');
});

$app->get('/signout', function () use ($app) {
    $app['session']->set('user', false);
    return $app->redirect('./');
});

$app->run();
