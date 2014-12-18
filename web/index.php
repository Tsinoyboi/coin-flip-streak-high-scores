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
    $sql = "SELECT id FROM user WHERE username = ? AND password = ?";
    $prepared = array(
        $username,
        $password,
    );
    $userResult = $app['db']->fetchAssoc($sql, $prepared);
    if (false === $userResult) {
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
	$username = $request->get('username');
	$password = $request->get('password');
    $confirm = $request->get('confirm');
    if ($password !== $confirm) {
        $user = $app['session']->get('user');
		return $app['twig']->render('register.twig', array(
            'username' => $username,
            'sessionuser' => $user['username'],
            'isConfirmed' => false,
            'userExists' => false,
        ));
	}
	$sql = "SELECT id FROM user WHERE username = ?";
    $prepared = array(
        $username,
    );
    $userResult = $app['db']->fetchAssoc($sql, $prepared);
    $user = $app['session']->get('user');
	if (false === $userResult) {
		$sql = "INSERT INTO user (username, password) VALUES (?, ?)";
	    $prepared = array(
            $username,
            $password,
        );
        $app['db']->executeUpdate($sql, $prepared);
        return $app['twig']->render('signin.twig', array(
		    'username' => $username,
            'sessionuser' => $user['username'],
		));
    } else {
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

$app->get('/highscores', function () use ($app) {
    $user = $app['session']->get('user');

    $sql = "SELECT DISTINCT us.username, st.length, fa.name as face_name";
    $sql .= " FROM streak st";
    $sql .= " JOIN user us";
    $sql .= " ON st.user_id = us.id";
    $sql .= " JOIN face fa";
    $sql .= " ON st.face_id = fa.id";
    $sql .= " ORDER BY length DESC";
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
        return $app['twig']->render('signin.twig', array(
            'username' => $username,
            'sessionuser' => $user['username'],
            'isValid' => true,
        ));
    }

    $sql = "SELECT DISTINCT us.username, st.length, fa.name as face_name";
    $sql .= " FROM streak st";
    $sql .= " JOIN user us";
    $sql .= " ON st.user_id = us.id";
    $sql .= " JOIN face fa";
    $sql .= " ON st.face_id = fa.id";
    $sql .= " ORDER BY length DESC";
    $sql .= " LIMIT 10";

    $prepared = array(
    );

    $recentFlipResult = $app['db']->fetchAll($sql, $prepared);

    $sql = "SELECT DISTINCT us.username, st.length, fa.name as face_name";
    $sql .= " FROM streak st";
    $sql .= " JOIN user us";
    $sql .= " ON st.user_id = us.id";
    $sql .= " JOIN face fa";
    $sql .= " ON st.face_id = fa.id";
    $sql .= " ORDER BY length DESC";
    $sql .= " LIMIT 10";

    $prepared = array(
    );

    $recentStreakResult = $app['db']->fetchAll($sql, $prepared);

    $sql = "SELECT DISTINCT us.username, st.length, fa.name as face_name";
    $sql .= " FROM streak st";
    $sql .= " JOIN user us";
    $sql .= " ON st.user_id = us.id";
    $sql .= " JOIN face fa";
    $sql .= " ON st.face_id = fa.id";
    $sql .= " ORDER BY length DESC";
    $sql .= " LIMIT 10";

    $prepared = array(
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
		return $app['twig']->render('signin.twig', array(
            'username' => $username,
            'sessionuser' => $user['username'],
            'isValid' => true,
		));
    }
    $sql = "SELECT id FROM user WHERE username = ?";
    $prepared = array(
        $user['username'],
    );
    $userResult = $app['db']->fetchAssoc($sql, $prepared);

    $sql = "SELECT face_id, streak_id FROM flip WHERE user_id = ? ORDER BY time_flipped DESC LIMIT 1";
    $prepared = array(
        $userResult['id'],
    );
    $flipResult = $app['db']->fetchAssoc($sql, $prepared);

    $sql = "SELECT name FROM face WHERE id = ?";
    $prepared = array(
        $flipResult['face_id'],
    );
    $faceResult = $app['db']->fetchAssoc($sql, $prepared);

    $sql = "SELECT length FROM streak WHERE id = ?";
    $prepared = array(
        $flipResult['streak_id'],
    );
    $streakResult = $app['db']->fetchAssoc($sql, $prepared);

    return $app['twig']->render('flip.twig', array(
        'sessionuser' => $user['username'],
        'lastflip' => $faceResult['name'],
        'streaklength' => $streakResult['length'],
    ));
});

$app->post('/flippate', function() use($app) {
    $user = $app['session']->get('user');
    if ((null === $user) || (false === $user)) {
        return $app['twig']->render('signin.twig', array(
            'username' => $username,
            'sessionuser' => $user['username'],
        ));
    } else {
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
        $faceResult = $app['db']->fetchAssoc($sql, $prepared);

        $user = $app['session']->get('user');

        $sql = "SELECT id FROM user WHERE username = ?";
        $prepared = array(
            $user['username'],
        );
        $userResult = $app['db']->fetchAssoc($sql, $prepared);

        $sql = "SELECT * FROM flip WHERE user_id = ? ORDER BY time_flipped DESC LIMIT 1";
        $prepared = array(
            $userResult['id'],
        );
        $flipResult = $app['db']->fetchAssoc($sql, $prepared);

        if ((false === $flipResult) || ($flipResult['face_id'] !== $faceResult['id'])) {
    		// start a new streak

    		$sql = "INSERT INTO streak (user_id, face_id, length) VALUES (?, ?, ?)";
    		$prepared = array(
    			$userResult['id'],
    			$faceResult['id'],
    			1,
    		);
    		$app['db']->executeUpdate($sql, $prepared);
    		$streakId = $app['db']->lastInsertId();

        } else {
    	    // make the streak longer
    		$streakId = $flipResult['streak_id'];
    		$sql = "SELECT length FROM streak WHERE id = ?";
    		$prepared = array(
    			$streakId,
    		);
    		$streakResult = $app['db']->fetchAssoc($sql, $prepared);
    		$sql = "UPDATE streak SET length = ? WHERE id = ?";
    	    $prepared = array(
                $streakResult['length'] + 1,
                $streakId,
            );
            $app['db']->executeUpdate($sql, $prepared);
        }
    	// new flip and attach to streak*/
    	$sql = "INSERT INTO flip (user_id, face_id, streak_id, time_flipped) VALUES (?, ?, ?, ?)";
    	$now = new \DateTime();
    	$prepared = array(
            $userResult['id'],
            $faceResult['id'],
            $streakId,
            $now->format('Y-m-d H:i:s'),
        );
        $app['db']->executeUpdate($sql, $prepared);

        return $app->redirect('./flip');
    }
});

$app->get('/signout', function () use ($app) {
	$app['session']->set('user', false);
    return $app->redirect('./');
});

$app->run();
