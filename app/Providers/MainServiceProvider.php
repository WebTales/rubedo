<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Alambic\Alambic;


class MainServiceProvider extends ServiceProvider
{
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
                    $alambicConfig["alambicConnectors"] = array_merge($alambicConfig["alambicConnectors"] , $jsonArray);
                }
                $modelFiles = glob($alambicDirPath.'/models/*.json');
                foreach ($modelFiles as $filePath) {
                    $tempJson = file_get_contents($filePath);
                    $jsonArray = json_decode($tempJson, true);
                    $alambicConfig["alambicTypeDefs"]  = array_merge($alambicConfig["alambicTypeDefs"], $jsonArray);
                }
            }
            return new Alambic($alambicConfig);
        });
    }
}
