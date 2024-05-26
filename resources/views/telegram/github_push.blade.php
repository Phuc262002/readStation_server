<b>☁️ [PUSH] - {{ $message['branch'] }}</b>

👨🏻‍💻 <b>[{{ $message['developer']['name'] }}]</b>(<a href="{{ $message['developer']['html_url'] }}">{{ $message['developer']['html_url'] }}</a>) pushed <b>{{ $message['commit']['commit_count'] }}</b> commits onto <a href="{{ $message['repository']['html_url'] }}">{{ $message['repository']['name'] }}</a>

<blockquote>🗣️ {{ $message['commit']['message'] }}</blockquote>

@foreach ($message['commit']['commit_changes'] as $item)
<pre><code class="language-json">[{{ $item['timestamp'] }}][{{ $item['author']['username'] }}][{{count($item['removed'])}} removed][{{ count($item['modified']) }} modified]
{{ $item['message'] }}</code></pre>
@endforeach
