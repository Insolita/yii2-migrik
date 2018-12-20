CHANGELOG
=================================
20.12.2018 - 3.1 improve postgres jsonb support; check on latest yii2 version

15.08.2018 - 3.0 version release; For yii >=2.0.14; add tinyInteger column support

29.04.2017 - 2.3 version release; Fix common bugs; seprate postgresql resolver for suppor arrays (one dimensional only) and json columns; fix view files;

03.02.2017 - 2.2.9 version release
 - Fix bug #28; upgrade tests for using YII2 codeception module fix #26

22.12.2016 - 2.2.7 version release
 - add fields for setup default values onUpdate and onDelete in relations

17.12.2016 - 2.2.6 version release
 - merge [pr](https://github.com/Insolita/yii2-migrik/pull/19) from [shirase](https://github.com/shirase) that fix issue with non-correct gii preview;
     
20.08.2016 - 2.2 version release
 - added new generator by phpdoc annotations; see [annotation syntax](#annotation-syntax)
 
15.08.2016 - 2.1 version release 
 - added ability to generate migrations in fluent interface (raw format also available)
 - improved templates; added database initializations
 - improved postresql index retrieving
 - structure improved; separate logic in external classes
 - added ability to set custom class for column generation (@see [Customizing section](#customizing))   
 
13.08.2016 - 2.0 version release with new ability
- generate migrations based on table data __Possible BC__
- class insolita\migrik\gii\Generator was changed on insolita\migrik\gii\StructureGenerator if you made template customizations - see your gii config and replace old Generator class name
