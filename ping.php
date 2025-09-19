<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = trim($_POST['url']);
    $blogName = trim($_POST['blogName']);
    $blogUrl = trim($_POST['blogUrl']);

    if (!filter_var($url, FILTER_VALIDATE_URL) || !filter_var($blogUrl, FILTER_VALIDATE_URL)) {
        die("❌ Invalid URL provided.");
    }

    echo "<h1>Ping Results</h1>";
    echo "<p><b>Sitemap URL:</b> $url</p>";
    echo "<p><b>Blog:</b> $blogName ($blogUrl)</p><hr>";

    // ---------- Sitemap Pings ----------
    $engines = [
        "Google"     => "https://www.google.com/ping?sitemap=" . urlencode($url),
        "Bing"       => "https://www.bing.com/ping?sitemap=" . urlencode($url),
        "Yandex"     => "https://webmaster.yandex.com/ping?sitemap=" . urlencode($url),
        "Baidu"      => "http://www.baidu.com/sitemap?url=" . urlencode($url),
        "Ask"        => "http://submissions.ask.com/ping?sitemap=" . urlencode($url),
        "IndexNow"   => "https://api.indexnow.org/IndexNow?url=" . urlencode($url)
    ];

    echo "<h2>🔎 Search Engine Pings</h2>";
    foreach ($engines as $name => $pingUrl) {
        $response = @file_get_contents($pingUrl);
        if ($response !== false) {
            echo "✅ Successfully pinged <b>$name</b><br>";
        } else {
            echo "⚠️ Could not reach <b>$name</b><br>";
        }
    }

    // ---------- Blog Pings ----------
    echo "<hr><h2>📰 Blog Pings</h2>";

    function sendBlogPing($server, $blogName, $blogUrl) {
        $xml = "<?xml version=\"1.0\"?>
        <methodCall>
          <methodName>weblogUpdates.ping</methodName>
          <params>
            <param><value><string>$blogName</string></value></param>
            <param><value><string>$blogUrl</string></value></param>
          </params>
        </methodCall>";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $server);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response ? true : false;
    }

    $blogServers = [
        "Ping-o-Matic" => "http://rpc.pingomatic.com/",
        "Twingly"      => "http://rpc.twingly.com/",
        "Feedburner"   => "http://ping.feedburner.com/"
    ];

    foreach ($blogServers as $name => $server) {
        if (sendBlogPing($server, $blogName, $blogUrl)) {
            echo "✅ Blog Ping sent to <b>$name</b><br>";
        } else {
            echo "⚠️ Failed to ping <b>$name</b><br>";
        }
    }

    echo "<hr><p><a href='index.html'>🔙 Back</a></p>";
}
?>