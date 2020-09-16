<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InvalidCredentialsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthLoginRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
	/**
	 * @var AuthService Authorization Service.
	 */
	private AuthService $authService;

	/**
	 * AuthController constructor.
	 *
	 * @param AuthService $authService Authorization Service.
	 */
	public function __construct(AuthService $authService) {
		$this->authService = $authService;
	}

	/**
	 * Return logged user.
	 *
	 * @param Request $request
	 *
	 * @return mixed
	 */
	public function user(Request $request) {
		return $request->user();
	}

	/**
	 * User login.
	 *
	 * @param AuthLoginRequest $request
	 *
	 * @return Response
	 */
	public function login(AuthLoginRequest $request) {
		try {
			$user = $this->authService->login($request->get('email'), $request->get('password'));
		} catch (InvalidCredentialsException $e) {
			return response(["message" => $e->getMessage()], $e->getCode());
		}

		return response($user);
	}

	/**
	 * Refresh access_token.
	 *
	 * @param Request $request
	 *
	 * @return Response
	 */
	public function refresh(Request $request) {
		$refresh_token = $request->cookie($this->authService::REFRESH_TOKEN);

		try {
			$token = $this->authService->refresh($refresh_token);
		} catch (InvalidCredentialsException $e) {
			return response(["message" => $e->getMessage()], $e->getCode());
		}

		return response($token);
	}

	/**
	 * Logout user.
	 *
	 * @param Request $request
	 *
	 * @return Response
	 */
	public function logout(Request $request) {
		$access_token = $request->user()->token()->id;

		$this->authService->logout($access_token);

		return response(null, 204);
	}
}
