<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Customer;
use Illuminate\Support\Str;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Response;

class AuthController extends Controller
{
  public function handleALBCallback(Request $request)
  {
    // Retrieve user information from ALB headers
    // you don't need $accessToken and $identity in this lab
    // $accessToken = $request->header('x-amzn-oidc-accesstoken');
    // $identity = $request->header('x-amzn-oidc-identity');
    $encodedJwt = $request->header('x-amzn-oidc-data');

    // Step 1: Get the key ID from JWT headers (the kid field)
    $jwtHeaders = explode('.', $encodedJwt)[0];
    $decodedJwtHeaders = base64_decode($jwtHeaders);
    $decodedJson = json_decode($decodedJwtHeaders, true);
    $kid = $decodedJson['kid'];

    // Step 2: Get the public key from the regional endpoint
    $region = 'us-east-1';
    $url = "https://public-keys.auth.elb.$region.amazonaws.com/$kid";
    $response = Http::get($url);
    $pubKey = $response->body();

    //step3: decode to get user infor
    $algorithms = 'ES256';
    $payload = JWT::decode($encodedJwt, new Key($pubKey, $algorithms));

    $userName = $payload->name;

    session()->put('userName', $userName);

   $token = file_get_contents('http://169.254.169.254/latest/api/token', false, stream_context_create([
    'http' => [
        'method' => 'PUT',
        'header' => "X-aws-ec2-metadata-token-ttl-seconds: 300",
    ],
]));

$instanceId = file_get_contents('http://169.254.169.254/latest/meta-data/instance-id', false, stream_context_create([
    'http' => [
        'header' => "X-aws-ec2-metadata-token: $token",
    ],
]));

    // Use the access token for further requests or store it in the session

    return view(
      'dashboard',
      [
        'instanceId' => $instanceId
      ]
    );
  }

  public function logout(Request $request)
  {
    //config Logout endpoint
    $request->session()->invalidate();
    $clientId = env('COGNITO_CLIENT_ID');
    $logoutUrl = env('COGNITO_LOGOUT_URL');
    $logoutRedirectUri = env('COGNITO_LOGOUT_REDIRECT_URI');
    $requestUrl = "{$logoutUrl}?client_id={$clientId}&logout_uri={$logoutRedirectUri}";

    // Delete ALB cookies
    $response = new Response();
    $response = $response->withCookie(Cookie::make('AWSELBAuthSessionCookie-0', null, -1));
    $response = $response->withCookie(Cookie::make('AWSELBAuthSessionCookie-1', null, -1));
    $response = $response->withCookie(Cookie::make('AWSALBAuthNonce', null, -1));

    // Add cache control headers
    $response = $response->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    $response = $response->header('Pragma', 'no-cache');
    $response = $response->header('Expires', '0');

    // Call logout endpoint
    $response->setStatusCode(302);
    $response->header('Location', $requestUrl);
    return $response;
  }
}
