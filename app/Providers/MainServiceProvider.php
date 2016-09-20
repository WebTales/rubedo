<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Alambic\Alambic;
use App\Services\ResourceResolver;
use Exception;


class MainServiceProvider extends ServiceProvider
{
    protected $jsonErrorMessages = [
        JSON_ERROR_NONE => 'No error has occurred',
        JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX => 'Syntax error',
        JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
    ];
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('GraphQLHandler', function ($app) {
            $alambicConfig=[
                "alambicConnectors"=>[

                ],
                "alambicTypeDefs"=>[

                ]
            ];
            foreach(config("alambicConfigPaths") as $alambicDirPath){
                $connectorFiles = glob($alambicDirPath.'/connectors/*.json');
                foreach ($connectorFiles as $filePath) {
                    $tempJson = file_get_contents($filePath);
                    $jsonArray = json_decode($tempJson, true);
                    if(!$jsonArray){
                        throw new Exception("JSON decode error in file ".$filePath." : ".$this->jsonErrorMessages[json_last_error()]);
                    }
                    $alambicConfig["alambicConnectors"] = array_merge($alambicConfig["alambicConnectors"] , $jsonArray);
                }
                $modelFiles = glob($alambicDirPath.'/models/*.json');
                foreach ($modelFiles as $filePath) {
                    $tempJson = file_get_contents($filePath);
                    $jsonArray = json_decode($tempJson, true);
                    if(!$jsonArray){
                        throw new Exception("JSON decode error in file ".$filePath." : ".$this->jsonErrorMessages[json_last_error()]);
                    }
                    $alambicConfig["alambicTypeDefs"]  = array_merge($alambicConfig["alambicTypeDefs"], $jsonArray);
                }
            }
            return new Alambic($alambicConfig);
        });

        $this->app->singleton('ResourceResolver', function ($app) {
            return new ResourceResolver(config("resourceNamespaces"));
        });
    }
}
