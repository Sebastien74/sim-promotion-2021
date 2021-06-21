<section id="environment" class="bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h2>Environment</h2>
                <p class="lead">In .env file set your environment configuration.</p>
                <pre class="language-markup">
<span class="token comment">### APP CONFIG ###</span>
<code class="language-markup">APP_ENV_NAME=local</code>
<code class="language-markup">APP_ENV=dev</code>
<code class="language-markup">APP_SECRET=mySecretKey</code>
</pre>

                <pre class="language-markup">
<span class="token comment">### PROTOCOLS ###</span>
<code class="language-markup">HTTP_PROTOCOL_PROD=https</code>
<code class="language-markup">HTTP_PROTOCOL_DEV=https</code>
<code class="language-markup">HTTP_PROTOCOL_LOCAL=http</code>
</pre>

                <pre class="language-markup">
<span class="token comment">### DATABASE LOCAL ###</span>
<code class="language-markup">DB_USER_LOCAL=root</code>
<code class="language-markup">DB_PASSWORD_LOCAL=</code>
<code class="language-markup">DB_HOST_LOCAL=localhost</code>
<code class="language-markup">DB_PORT_LOCAL=</code>
<code class="language-markup">DB_NAME_LOCAL=mydbname</code>
</pre>

                <pre class="language-markup">
<span class="token comment">### DATABASE SERVER ###</span>
<code class="language-markup">DB_USER_SERVER=</code>
<code class="language-markup">DB_PASSWORD_SERVER=</code>
<code class="language-markup">DB_HOST_SERVER=</code>
<code class="language-markup">DB_PORT_SERVER=</code>
<code class="language-markup">DB_NAME_SERVER=</code>
</pre>

                <pre class="language-markup">
<span class="token comment">### SWIFTMAILER ###</span>
<code class="language-markup">SWIFT_TRANSPORT=sendmail</code>
<code class="language-markup">SWIFT_USERNAME=</code>
<code class="language-markup">SWIFT_PASSWORD=</code>
<code class="language-markup">SWIFT_HOST=</code>
<code class="language-markup">SWIFT_PORT=</code>
</pre>
            </div>
        </div>
    </div>
</section>