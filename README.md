Kohana module wrapping [bit.ly](http://bit.ly)'s [shorten](http://code.google.com/p/bitly-api/wiki/ApiDocumentation#/v3/shorten) method.

**Usage**

	$short_url = Bitly::instance()->shorten($long_url);

If there is an error, (rate limit exceeded, etc.) the original (long) URL is returned and the error is logged.
