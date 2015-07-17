<?php

namespace PhpDraft\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use PhpDraft\Domain\Models\PhpDraftResponse;

class TradeController {
  public function GetAll(Application $app, Request $request) {
    $draft_id = (int)$request->get('draft_id');

    $viewable = $app['phpdraft.DraftValidator']->IsDraftViewableForUser($draft_id, $request);

    if(!$viewable) {
      $response = new PhpDraftResponse(false, array());
      $response->errors[] = "Draft marked as private.";

      return $app->json($response, Response::HTTP_BAD_REQUEST);
    }

    $in_progress = $app['phpdraft.DraftValidator']->IsDraftInProgress($draft);

    if(!$in_progress->success) {
      return $app->json($in_progress, Response::HTTP_BAD_REQUEST);
    }

    return $app->json($app['phpdraft.TradeRepository']->GetTrades($draft_id), Response::HTTP_OK);
  }
}