<?php

namespace App\Services;

use App\Models\User;
use App\Exceptions\InvalidCredentialsException;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;

class AuthService
{
	const ACCESS_TOKEN = 'access_token';
	const REFRESH_TOKEN = 'refresh_token';

	/**
	 * Attempt login.
	 *
	 * @param string $email Email.
	 * @param string $password Password.
	 *
	 * @return object Returns user data object.
	 * @throws InvalidCredentialsException Throws an exception on unseccessfull credentials validation.
	 */
	public function login(string $email, string $password)
	{
		$user = User::where('email', $email)->first();

		if (is_null($user)) {
			throw new InvalidCredentialsException(trans('auth.failed'), 401);
		}

		$data = $this->send_token_request("password", [
			"username" => $email,
			"password" => $password
		]);

		if (!$data) {
			throw new InvalidCredentialsException(trans('auth.failed'), 401);
		}

		$user['expires_in'] = $data->expires_in;

		return $user;
	}

	/**
	 * Attempt refresh access_token.
	 *
	 * @param string $refresh_token Refresh token.
	 *
	 * @return object Returns object with token expiration data.
	 * @throws InvalidCredentialsException Throws an exception on unseccessfull token validation.
	 */
	public function refresh(string $refresh_token)
	{
		if (!$refresh_token) {
			throw new InvalidCredentialsException(trans('auth.failed'), 401);
		}

		$data = $this->send_token_request("refresh_token", [
			"refresh_token" => $refresh_token
		]);

		if (!$data) {
			throw new InvalidCredentialsException(trans('auth.failed'), 401);
		}

		$token['expires_in'] = $data->expires_in;

		return $token;
	}

	/**
	 * Logout & revoke tokens.
	 *
	 * @param string $access_token Access token ID.
	 */
	public function logout(string $access_token)
	{
		$tokenRepository = app('Laravel\Passport\TokenRepository');
		$refreshTokenRepository = app('Laravel\Passport\RefreshTokenRepository');

		$tokenRepository->revokeAccessToken($access_token);
		$refreshTokenRepository->revokeRefreshTokensByAccessTokenId($access_token);

		Cookie::queue(Cookie::make(self::ACCESS_TOKEN, null));
		Cookie::queue(Cookie::make(self::REFRESH_TOKEN, null));
	}

	/**
	 * Send token request.
	 *
	 * @param string $type Request type. password|refresh_token
	 * @param array  $data Request data.
	 *
	 * @return false|object Returns object of respone data on successful request and false on unseccessfull.
	 */
	private function send_token_request(string $type, array $data)
	{
		$data = array_merge($data, [
			'client_id'     => config('auth.password_client_id'),
			'client_secret' => config('auth.password_client_secret'),
			'grant_type'    => $type
		]);

		$response = Http::post(config('app.url')."/oauth/token", $data);

		if (!$response->successful()) {
			return false;
		}

		$response = (object)$response->json();

		Cookie::queue(Cookie::make(self::ACCESS_TOKEN, $response->access_token, 5, null, null, false, false));
		Cookie::queue(Cookie::make(self::REFRESH_TOKEN, $response->refresh_token, 864000, null, null, false, false));

		return $response;
	}
}
