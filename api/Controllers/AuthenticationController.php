<?php

namespace PhpDraft\Controllers;

use \Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use PhpDraft\Domain\Entities\LoginUser;

class AuthenticationController
{
  //See Commish->Index for permissions check

  public function Login(Application $app, Request $request) {
    $vars = json_decode($request->getContent(), true);
    $username = $request->get('_username');
    $password = $request->get('_password');

    try {
      if (empty($username) || empty($password)) {
        throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
      }

      $user = $app['users']->loadUserByUsername($username);

      if (!$user->isEnabled() || !$app['security.encoder.digest']->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
        throw new UsernameNotFoundException(sprintf('Username "%s" does not exist', $username));
      } else {
        $response = [
          'success' => true,
          'token' => $app['security.jwt.encoder']->encode(['name' => $user->getUsername()]),
        ];
      }
    } catch (UsernameNotFoundException $e) {
      $response = [
        'success' => false,
        'errors' => 'Invalid credentials.',
      ];
    }

    $responseType = ($response['success'] == true ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);

    return $app->json($response, $responseType);
  }

  public function Register(Application $app, Request $request) {
    $validity = $app['phpdraft.LoginUserValidator']->IsRegistrationUserValid($request);

    if(!$validity->success) {
      return $app->json($validity, Response::HTTP_BAD_REQUEST);
    }

    $user = new LoginUser();

    $user->username = $request->get('_username');
    $user->email = $request->get('_email');
    $user->password = $request->get('_password');
    $user->name = $request->get('_name');
    
    $response = $app['phpdraft.LoginUserService']->CreateUnverifiedNewUser($user);

    $responseType = ($response->success == true ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);

    return $app->json($response, $responseType);
  }

  public function VerifyAccount(Application $app, Request $request) {
    $validity = $app['phpdraft.LoginUserValidator']->IsVerificationValid($request);

    if(!$validity->success) {
      return $app->json($validity, Response::HTTP_BAD_REQUEST);
    }

    $username = urldecode($request->get('_username'));

    $user = $app['phpdraft.LoginUserRepository']->Load($username);

    $response = $app['phpdraft.LoginUserService']->VerifyUser($user);

    $responseType = ($response->success == true ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);

    return $app->json($response, $responseType);
  }

  public function LostPassword(Application $app, Request $request) {
    //Starts lost pwd process - set verification key & send email
  }

  public function ResetPassword(Application $app, Request $request) {
    //Finishes lost pwd process - given proper verification key, reset the user's password.
  }
}