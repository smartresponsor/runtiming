Optional integration into /status

If your /status endpoint already returns a JSON payload, inject RuntimeEngineDetectorInterface and add:

"engine": $detector->getEngineName()

This sketch doesn't change your existing status controller/handler to avoid merge conflicts.
