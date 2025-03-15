
(function (d, t) {
  var g = d.createElement(t),
      s = d.getElementsByTagName(t)[0];

  g.async = true;
  g.defer = true;
  g.src = chatwootSettings.url + "/packs/js/sdk.js";
  s.parentNode.insertBefore(g, s);

  g.onload = function () {
    window.chatwootSDK.run({
      websiteToken: chatwootSettings.token,
      baseUrl: chatwootSettings.url,
    });

    // ✅ Set widget options globally
    window.chatwootSettings = {
      ...chatwootSettings // Optional in case needed inside SDK hooks
    };

    // ✅ Identity validation via HMAC (if injected from PHP)
    console.log("window.chatwootIdentity:", window.chatwootIdentity); // Add this line before the event listener
    window.ChatwootDebug = true; // Ensure this is set before the event listener
    window.addEventListener("chatwoot:ready", function () {
      console.log("chatwoot:ready event fired"); // Add this line
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

        if (window.ChatwootDebug) {
          console.group("🔐 Chatwoot Identity Debug");
          console.log("🆔 Identifier:", window.chatwootIdentity.identifier);
          console.log("🔑 HMAC Hash:", window.chatwootIdentity.hash);
          if (window.chatwootIdentity.name) console.log("👤 Name:", window.chatwootIdentity.name);
          if (window.chatwootIdentity.email) console.log("✉️ Email:", window.chatwootIdentity.email);
          console.groupEnd();
        }
      } else {
        if (window.ChatwootDebug) {
          console.warn("⚠️ Chatwoot identity not available or incomplete.");
        }
      }
    });
  };
})(document, "script");
