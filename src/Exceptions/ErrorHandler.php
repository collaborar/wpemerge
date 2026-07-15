<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Exceptions;

use Exception as PhpException;
use Psr\Http\Message\ResponseInterface;
use Whoops\RunInterface;
use WPEmerge\Csrf\InvalidCsrfTokenException;
use WPEmerge\Requests\RequestInterface;
use WPEmerge\Responses\ResponseService;
use WPEmerge\Routing\NotFoundException;
use WPEmerge\Support\Arr;

class ErrorHandler implements ErrorHandlerInterface {
	/**
	 * Response service.
	 */
	protected ResponseService $response_service;

	/**
	 * Pretty handler.
	 */
	protected ?RunInterface $whoops = null;

	/**
	 * Whether debug mode is enabled.
	 */
	protected bool $debug = false;

	/**
	 * Constructor.
	 *
	 * @codeCoverageIgnore
	 */
	public function __construct(
		ResponseService $response_service,
		?RunInterface $whoops = null,
		bool $debug = false
	) {
		$this->response_service = $response_service;
		$this->whoops = $whoops;
		$this->debug = $debug;
	}

	/**
	 * {@inheritDoc}
	 * @codeCoverageIgnore
	 */
	public function register(): void {
		if ( $this->debug && $this->whoops !== null ) {
			$this->whoops->register();
		}
	}

	/**
	 * {@inheritDoc}
	 * @codeCoverageIgnore
	 */
	public function unregister(): void {
		if ( $this->debug && $this->whoops !== null ) {
			$this->whoops->unregister();
		}
	}

	/**
	 * Convert an exception to a ResponseInterface instance if possible.
	 *
	 * @param  PhpException            $exception
	 * @return ResponseInterface|false
	 */
	protected function toResponse( PhpException $exception ): ResponseInterface|false {
		// @codeCoverageIgnoreStart
		if ( $exception instanceof InvalidCsrfTokenException ) {
			wp_nonce_ays( '' );
		}
		// @codeCoverageIgnoreEnd

		if ( $exception instanceof NotFoundException ) {
			return $this->response_service->error( 404 );
		}

		return false;
	}

	/**
	 * Convert an exception to a debug ResponseInterface instance if possible.
	 *
	 * @throws PhpException
	 * @param  RequestInterface  $request
	 * @param  PhpException      $exception
	 * @return ResponseInterface
	 */
	protected function toDebugResponse( RequestInterface $request, PhpException $exception ): ResponseInterface {
		if ( $request->isAjax() ) {
			return $this->response_service->json( [
				'message' => $exception->getMessage(),
				'exception' => get_class( $exception ),
				'file' => $exception->getFile(),
				'line' => $exception->getLine(),
				'trace' => array_map( function ( $trace ) {
					return Arr::except( $trace, ['args'] );
				}, $exception->getTrace() ),
			] )->withStatus( 500 );
		}

		if ( $this->whoops !== null ) {
			return $this->toPrettyErrorResponse( $exception );
		}

		throw $exception;
	}

	/**
	 * Convert an exception to a pretty error response.
	 *
	 * @codeCoverageIgnore
	 * @param  PhpException      $exception
	 * @return ResponseInterface
	 */
	protected function toPrettyErrorResponse( PhpException $exception ): ResponseInterface {
		$method = RunInterface::EXCEPTION_HANDLER;
		ob_start();
		$this->whoops->$method( $exception );
		$response = ob_get_clean();
		return $this->response_service->output( $response )->withStatus( 500 );
	}

	/**
	 * {@inheritDoc}
	 * @throws PhpException
	 */
	public function getResponse( RequestInterface $request, PhpException $exception ): ResponseInterface {
		$response = $this->toResponse( $exception );

		if ( $response !== false ) {
			return $response;
		}

		// @codeCoverageIgnoreStart
		if ( ! defined( 'WPEMERGE_TEST_DIR' ) ) {
			// Only log errors if we are not running the WP Emerge test suite.
			error_log( $exception );
		}
		// @codeCoverageIgnoreEnd

		if ( ! $this->debug ) {
			return $this->response_service->error( 500 );
		}

		return $this->toDebugResponse( $request, $exception );
	}
}
