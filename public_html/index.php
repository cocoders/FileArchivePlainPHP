<?php

use Cocoders\PdoConnection;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Translator;

require __DIR__.'/../vendor/autoload.php';

define('VENDOR_DIR', realpath(__DIR__ . '/../vendor'));
define('VENDOR_FORM_DIR', VENDOR_DIR . '/symfony/form/Symfony/Component/Form');
define('VENDOR_VALIDATOR_DIR', VENDOR_DIR . '/symfony/validator/Symfony/Component/Validator');
define('VENDOR_TWIG_BRIDGE_DIR', VENDOR_DIR . '/symfony/twig-bridge/Symfony/Bridge/Twig');
define('VIEWS_DIR', realpath(__DIR__ . '/../views'));
define('DEFAULT_FORM_THEME', 'form_div_layout.html.twig');

$twig = new \Twig_Environment(new Twig_Loader_Filesystem(array(
    VIEWS_DIR,
    VENDOR_TWIG_BRIDGE_DIR . '/Resources/views/Form',
)));

$translator = new Translator('en');
$translator->addLoader('xlf', new XliffFileLoader());
$translator->addResource('xlf', VENDOR_FORM_DIR . '/Resources/translations/validators.en.xlf', 'en', 'validators');
$translator->addResource('xlf', VENDOR_VALIDATOR_DIR . '/Resources/translations/validators.en.xlf', 'en', 'validators');

$formEngine = new TwigRendererEngine(array(DEFAULT_FORM_THEME));
$formEngine->setEnvironment($twig);
$twig->addExtension(new TranslationExtension($translator));
$twig->addExtension(new FormExtension(new TwigRenderer($formEngine)));

$module = (@$_GET['module']) ?: 'archive';
$action = (@$_GET['action']) ?: 'create';

$controllerClassName = sprintf('Cocoders\\Controller\\%s', ucfirst($module));
if (!class_exists($controllerClassName)) {
    $response = new Response('', 404);
    $response->send();
    return;
}
$connection = new PdoConnection('pgsql:host=localhost;port=5432;dbname=filearchive-examples;user=user;password=user');
$controllerClass = new $controllerClassName($twig, $connection);

$controllerCallable = [$controllerClass, $action];
if (!is_callable($controllerCallable)) {
    $response = new Response('', 404);
    $response->send();
    return;
}

$response = call_user_func_array($controllerCallable, [Request::createFromGlobals()]);
if (!($response instanceof Response)) {
    throw new \Exception('Controller should return Symfony\Component\HttpFoundation\Response object');
}
$response->send();
return;
