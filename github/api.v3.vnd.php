<?php
$error = false;

if ( ! $path = $_GET['path'] ) {
	$error = true;
}

if ( ! $token = $_GET['access_token'] ) {
	$error = true;
}

if ( ! $media_type = urldecode( $_GET['media_type'] ) ) {
	$error = true;
}

if ( $error ) {
	?>
	<h1>Usage</h1>
	<p>This script requires the following query parameters:</p>
	<ul>
		<li><code>access_token</code> (required, see
			<a href="http://developer.github.com/v3/#authentication">Authentication</a>)
		</li>
		<li><code>path</code> (required, see <a href="http://developer.github.com/v3/issues/">Issues</a>)</li>
		<li><code>media_type</code> (required, see
			<a href="http://developer.github.com/v3/media/">Media Types</a>; note: this must be properly URL encoded;
			<code>html+json</code> should be passed as <code>html%2Bjson</code>)
		</li>
	</ul>
	<p>
		<strong>Note:</strong> Other query variables passed to this script will be appended to the API call, so you can also include things like
		<code>since</code>, <code>direction</code>, and <code>sort</code> (applicable when querying
				<a href="http://developer.github.com/v3/issues/">Issues</a>).</p>
	<p>Here's an example that queries the <code>habari</code> repo, which is owned by the <code>habari</code> user, for all Issue Comments created since <code>2014-01-09T00:47+0000</code>, sorted by <code>created</code>, ordered by <code>desc</code>, with the comment body returned in HTML format:</p>
	<p>
		<code>https://tools.raam.org/github/api.v3.vnd.php?path=repos/habari/habari/issues/comments/&media_type=html%2Bjson&direction=desc&sort=created&since=2014-01-09T00%3A47%2B0000&access_token=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx</code>
	</p>
	<h2>About this script</h2>
	<p>This script makes a cURL request to <code>https://api.github.com</code> and includes a Media Type header (e.g.,
		<code>Accept: application/vnd.github.v3.html+json</code>), which tells GitHub to return the request with content formatted with that media type, as opposed to the default format of
		<code>raw+json</code>.</p>
	<p>Acceptable media types that can be passed to this script are <code>raw+json</code>, <code>text+json</code>,
		<code>html+json</code>, and <code>full+json</code> (see
		<a href="http://developer.github.com/v3/media/">GitHub API Media Types</a>).</p>
	<p>HTML-formatted content is useful when building
		<a href="http://raam.org/1">an RSS feed using results from a GitHub API</a>.</p>
	<?php
	exit;
}

// We got this far, so proceed with unsetting the already assigned query vars and building a query with the remaining vars
unset( $_GET['path'] );
unset( $_GET['access_token'] );
unset( $_GET['media_type'] );
$query_vars = http_build_query( $_GET );

function get_json( $url, $token, $media_type ) {
	$base      = "https://api.github.com/";
	$curl      = curl_init();
	$headers[] = "Authorization: token $token";
	$headers[] = "Accept: application/vnd.github.v3." . $media_type;

	curl_setopt( $curl, CURLOPT_URL, $base . $url );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
	curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
	curl_setopt( $curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 GTB5' );
	$content = curl_exec( $curl );
	curl_close( $curl );

	return $content;
}

header( 'Content-Type: application/json' );
print get_json( $path . '?' . $query_vars, $token, $media_type );
?>