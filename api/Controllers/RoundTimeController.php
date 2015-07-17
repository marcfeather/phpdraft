<?php

namespace PhpDraft\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PhpDraft\Domain\Models\PhpDraftResponse;

class RoundTimeController {
  public function GetTimeRemaining(Application $app, Request $request) {
    $draft_id = (int)$request->get('draft_id');

    $viewable = $app['phpdraft.DraftValidator']->IsDraftViewableForUser($draft_id, $request);

    if(!$viewable) {
      $response = new PhpDraftResponse(false, array());
      $response->errors[] = "Draft marked as private.";

      return $app->json($response, Response::HTTP_BAD_REQUEST);
    }

    $draft = $app['phpdraft.DraftRepository']->Load($draft_id);

    $response = $app['phpdraft.RoundTimeService']->GetCurrentPickTimeRemaining($draft);
    $response_type = ($response->success ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);

    return $app->json($response, $response_type);
  }
}