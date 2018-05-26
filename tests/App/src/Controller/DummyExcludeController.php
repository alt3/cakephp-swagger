<?php
/**
 * Dummy test_app controller only used for crawl-generating a swagger document.
 *
 *
    @SWG\Get(
        path="/taxis",
        summary="Retrieve a list of taxis after drinking cocktails",
        tags={"taxi"},
        consumes={"application/json"},
        produces={"application/json"},
        @SWG\Response(
            response="200",
            description="Successful operation",
        )
    )
 */
class DummyExcludeController
{

}
