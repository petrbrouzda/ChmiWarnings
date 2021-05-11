<?php

declare(strict_types=1);

namespace PHPSTORM_META;

registerArgumentsSet('nette_http_codes',
	\Nette\Http\IResponse::S100_CONTINUE,
	\Nette\Http\IResponse::S101_SWITCHING_PROTOCOLS,
	\Nette\Http\IResponse::S102_PROCESSING,
	\Nette\Http\IResponse::S200_OK,
	\Nette\Http\IResponse::S201_CREATED,
	\Nette\Http\IResponse::S202_ACCEPTED,
	\Nette\Http\IResponse::S203_NON_AUTHORITATIVE_INFORMATION,
	\Nette\Http\IResponse::S204_NO_CONTENT,
	\Nette\Http\IResponse::S205_RESET_CONTENT,
	\Nette\Http\IResponse::S206_PARTIAL_CONTENT,
	\Nette\Http\IResponse::S207_MULTI_STATUS,
	\Nette\Http\IResponse::S208_ALREADY_REPORTED,
	\Nette\Http\IResponse::S226_IM_USED,
	\Nette\Http\IResponse::S300_MULTIPLE_CHOICES,
	\Nette\Http\IResponse::S301_MOVED_PERMANENTLY,
	\Nette\Http\IResponse::S302_FOUND,
	\Nette\Http\IResponse::S303_SEE_OTHER,
	\Nette\Http\IResponse::S303_POST_GET,
	\Nette\Http\IResponse::S304_NOT_MODIFIED,
	\Nette\Http\IResponse::S305_USE_PROXY,
	\Nette\Http\IResponse::S307_TEMPORARY_REDIRECT,
	\Nette\Http\IResponse::S308_PERMANENT_REDIRECT,
	\Nette\Http\IResponse::S400_BAD_REQUEST,
	\Nette\Http\IResponse::S401_UNAUTHORIZED,
	\Nette\Http\IResponse::S402_PAYMENT_REQUIRED,
	\Nette\Http\IResponse::S403_FORBIDDEN,
	\Nette\Http\IResponse::S404_NOT_FOUND,
	\Nette\Http\IResponse::S405_METHOD_NOT_ALLOWED,
	\Nette\Http\IResponse::S406_NOT_ACCEPTABLE,
	\Nette\Http\IResponse::S407_PROXY_AUTHENTICATION_REQUIRED,
	\Nette\Http\IResponse::S408_REQUEST_TIMEOUT,
	\Nette\Http\IResponse::S409_CONFLICT,
	\Nette\Http\IResponse::S410_GONE,
	\Nette\Http\IResponse::S411_LENGTH_REQUIRED,
	\Nette\Http\IResponse::S412_PRECONDITION_FAILED,
	\Nette\Http\IResponse::S413_REQUEST_ENTITY_TOO_LARGE,
	\Nette\Http\IResponse::S414_REQUEST_URI_TOO_LONG,
	\Nette\Http\IResponse::S415_UNSUPPORTED_MEDIA_TYPE,
	\Nette\Http\IResponse::S416_REQUESTED_RANGE_NOT_SATISFIABLE,
	\Nette\Http\IResponse::S417_EXPECTATION_FAILED,
	\Nette\Http\IResponse::S421_MISDIRECTED_REQUEST,
	\Nette\Http\IResponse::S422_UNPROCESSABLE_ENTITY,
	\Nette\Http\IResponse::S423_LOCKED,
	\Nette\Http\IResponse::S424_FAILED_DEPENDENCY,
	\Nette\Http\IResponse::S426_UPGRADE_REQUIRED,
	\Nette\Http\IResponse::S428_PRECONDITION_REQUIRED,
	\Nette\Http\IResponse::S429_TOO_MANY_REQUESTS,
	\Nette\Http\IResponse::S431_REQUEST_HEADER_FIELDS_TOO_LARGE,
	\Nette\Http\IResponse::S451_UNAVAILABLE_FOR_LEGAL_REASONS,
	\Nette\Http\IResponse::S500_INTERNAL_SERVER_ERROR,
	\Nette\Http\IResponse::S501_NOT_IMPLEMENTED,
	\Nette\Http\IResponse::S502_BAD_GATEWAY,
	\Nette\Http\IResponse::S503_SERVICE_UNAVAILABLE,
	\Nette\Http\IResponse::S504_GATEWAY_TIMEOUT,
	\Nette\Http\IResponse::S505_HTTP_VERSION_NOT_SUPPORTED,
	\Nette\Http\IResponse::S506_VARIANT_ALSO_NEGOTIATES,
	\Nette\Http\IResponse::S507_INSUFFICIENT_STORAGE,
	\Nette\Http\IResponse::S508_LOOP_DETECTED,
	\Nette\Http\IResponse::S510_NOT_EXTENDED,
	\Nette\Http\IResponse::S511_NETWORK_AUTHENTICATION_REQUIRED
);

registerArgumentsSet('nette_same_site',
	\Nette\Http\IResponse::SAME_SITE_LAX,
	\Nette\Http\IResponse::SAME_SITE_STRICT,
	\Nette\Http\IResponse::SAME_SITE_NONE
);

expectedArguments(\Nette\Http\IResponse::setCode(), 0, argumentsSet('nette_http_codes'));
expectedReturnValues(\Nette\Http\IResponse::getCode(), argumentsSet('nette_http_codes'));
expectedArguments(\Nette\Http\IResponse::setCookie(), 7, argumentsSet('nette_same_site'));
expectedArguments(\Nette\Http\Session::setCookieParameters(), 3, argumentsSet('nette_same_site'));
