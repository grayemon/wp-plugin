(function (d, t) {
  var g = d.createElement(t),
      s = d.getElementsByTagName(t)[0];

  g.async = true;
  g.defer = true;
  g.src = chatwootSettings.url + "/packs/js/sdk.js";
  s.parentNode.insertBefore(g, s);

  g.onload = function () {
    // ✅ Init Chatwoot SDK
    window.chatwootSDK.run({
      websiteToken: chatwootSettings.token,
      baseUrl: chatwootSettings.url,
    });

    // ✅ Global settings object (optional)
    window.chatwootSettings = {
      ...chatwootSettings
    };

    // ✅ Respect PHP debug flag
    window.ChatwootDebug = !!chatwootSettings.debug;

    // ✅ Listen to Chatwoot ready event
    window.addEventListener("chatwoot:ready", function () {
      if (
        window.chatwootIdentity &&
        window.chatwootIdentity.identifier &&
        window.chatwootIdentity.hash
      ) {
        window.$chatwoot.setUser(window.chatwootIdentity.identifier, {
          identifier_hash: window.chatwootIdentity.hash,
          name: window.chatwootIdentity.name || undefined,
          email: window.chatwootIdentity.email || undefined,
        });

        // ✅ Debugging output
        if (window.ChatwootDebug) {
          console.group("🔐 Chatwoot Identity Debug");
          console.log("🆔 Identifier:", window.chatwootIdentity.identifier);
          console.log("🔑 HMAC Hash:", window.chatwootIdentity.hash);
          if (window.chatwootIdentity.name)
            console.log("👤 Name:", window.chatwootIdentity.name);
          if (window.chatwootIdentity.email)
            console.log("✉️ Email:", window.chatwootIdentity.email);
          console.groupEnd();
        }
      } else {
        if (window.ChatwootDebug) {
          console.warn("⚠️ Chatwoot identity not available or incomplete.");
        }
      }
    });

    // ✅ Optional: capture Chatwoot widget errors
    if (window.ChatwootDebug) {
      window.addEventListener("chatwoot:error", function (e) {
        console.error("❌ Chatwoot widget error:", e);
      });
    }
  };
})(document, "script");
