<?php

/**
 * Dummy test_app controller only used for crawl-generating a swagger document.
 *
 *
    @SWG\Swagger(
        @SWG\Info(
            title="cakephp-swagger",
            description="cakephp-swagger test document",
            termsOfService="http://swagger.io/terms/",
            version="1.0.0"
        )
    )

    @SWG\Get(
        path="/cocktails",
        summary="Retrieve a list of cocktails",
        tags={"cocktail"},
        consumes={"application/json"},
        produces={"application/json"},
        @SWG\Parameter(
            name="sort",
            description="Sort results by field",
            in="query",
            required=false,
            type="string",
            enum={"name", "description"}
        ),
        @SWG\Response(
            response="200",
            description="Successful operation",
            @SWG\Schema(
                type="array",
                items=@SWG\Schema(ref="#/definitions/Cocktail")
            )
        ),
        @SWG\Response(
            response=429,
            description="Rate Limit Exceeded"
        )
    )

    @SWG\Definition(
        definition="Cocktail",
        required={"name", "description"},
        @SWG\Property(
            property="id",
            type="integer",
            description="CakePHP record id"
        ),
        @SWG\Property(
            property="name",
            type="string",
            description="CakePHP name field"
        ),
        @SWG\Property(
            property="description",
            type="string",
            description="Description of a most tasty cocktail"
        )
    )
 */
class DummyIncludeController
{

}
