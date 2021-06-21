<?php

declare(strict_types=1);

namespace PackageVersions;

use Composer\InstalledVersions;
use OutOfBoundsException;

class_exists(InstalledVersions::class);

/**
 * This class is generated by composer/package-versions-deprecated, specifically by
 * @see \PackageVersions\Installer
 *
 * This file is overwritten at every run of `composer install` or `composer update`.
 *
 * @deprecated in favor of the Composer\InstalledVersions class provided by Composer 2. Require composer-runtime-api:^2 to ensure it is present.
 */
final class Versions
{
    /**
     * @deprecated please use {@see self::rootPackageName()} instead.
     *             This constant will be removed in version 2.0.0.
     */
    const ROOT_PACKAGE_NAME = '__root__';

    /**
     * Array of all available composer packages.
     * Dont read this array from your calling code, but use the \PackageVersions\Versions::getVersion() method instead.
     *
     * @var array<string, string>
     * @internal
     */
    const VERSIONS          = array (
  'amphp/amp' => 'v2.5.2@efca2b32a7580087adb8aabbff6be1dc1bb924a9',
  'amphp/byte-stream' => 'v1.8.1@acbd8002b3536485c997c4e019206b3f10ca15bd',
  'amphp/cache' => 'v1.4.0@e7bccc526fc2a555d59e6ee8380eeb39a95c0835',
  'amphp/dns' => 'v1.2.3@852292532294d7972c729a96b49756d781f7c59d',
  'amphp/hpack' => 'v3.1.0@0dcd35f9a8d9fc04d5fb8af0aeb109d4474cfad8',
  'amphp/http' => 'v1.6.3@e2b75561011a9596e4574cc867e07a706d56394b',
  'amphp/http-client' => 'v4.5.5@ac286c0a2bf1bf175b08aa89d3086d1e9be03985',
  'amphp/parser' => 'v1.0.0@f83e68f03d5b8e8e0365b8792985a7f341c57ae1',
  'amphp/process' => 'v1.1.1@b88c6aef75c0b22f6f021141dd2d5e7c5db4c124',
  'amphp/serialization' => 'v1.0.0@693e77b2fb0b266c3c7d622317f881de44ae94a1',
  'amphp/socket' => 'v1.1.3@b9064b98742d12f8f438eaf73369bdd7d8446331',
  'amphp/sync' => 'v1.4.0@613047ac54c025aa800a9cde5b05c3add7327ed4',
  'amphp/windows-registry' => 'v0.3.3@0f56438b9197e224325e88f305346f0221df1f71',
  'behat/transliterator' => 'v1.3.0@3c4ec1d77c3d05caa1f0bf8fb3aae4845005c7fc',
  'cms/faker' => 'dev-master@53f59cfd56a9508578577c54a9ec470f050f5abe',
  'cms/phone-number-bundle' => 'dev-master@f88bb618aa35441edc6524a6e085b95a9c577501',
  'composer/ca-bundle' => '1.2.10@9fdb22c2e97a614657716178093cd1da90a64aa8',
  'composer/package-versions-deprecated' => '1.11.99.2@c6522afe5540d5fc46675043d3ed5a45a740b27c',
  'daverandom/libdns' => 'v2.0.2@e8b6d6593d18ac3a6a14666d8a68a4703b2e05f9',
  'doctrine/annotations' => '1.13.1@e6e7b7d5b45a2f2abc5460cc6396480b2b1d321f',
  'doctrine/cache' => '1.11.3@3bb5588cec00a0268829cc4a518490df6741af9d',
  'doctrine/collections' => '1.6.7@55f8b799269a1a472457bd1a41b4f379d4cfba4a',
  'doctrine/common' => '3.1.2@a036d90c303f3163b5be8b8fde9b6755b2be4a3a',
  'doctrine/dbal' => '2.13.1@c800380457948e65bbd30ba92cc17cda108bf8c9',
  'doctrine/deprecations' => 'v0.5.3@9504165960a1f83cc1480e2be1dd0a0478561314',
  'doctrine/doctrine-bundle' => '2.4.2@4202ce675d29e70a8b9ee763bec021b6f44caccb',
  'doctrine/doctrine-migrations-bundle' => '3.1.1@91f0a5e2356029575f3038432cc188b12f9d5da5',
  'doctrine/event-manager' => '1.1.1@41370af6a30faa9dc0368c4a6814d596e81aba7f',
  'doctrine/inflector' => '2.0.3@9cf661f4eb38f7c881cac67c75ea9b00bf97b210',
  'doctrine/instantiator' => '1.4.0@d56bf6102915de5702778fe20f2de3b2fe570b5b',
  'doctrine/lexer' => '1.2.1@e864bbf5904cb8f5bb334f99209b48018522f042',
  'doctrine/migrations' => '3.1.3@8d0c58655cf9d76462d00ec5dd099737e513e86d',
  'doctrine/orm' => '2.9.3@82e77cf5089a1303733f75f0f0ed01be3ab9ec22',
  'doctrine/persistence' => '2.2.1@d138f3ab5f761055cab1054070377cfd3222e368',
  'doctrine/sql-formatter' => '1.1.1@56070bebac6e77230ed7d306ad13528e60732871',
  'dragonmantank/cron-expression' => 'v3.1.0@7a8c6e56ab3ffcc538d05e8155bb42269abf1a0c',
  'egulias/email-validator' => '2.1.25@0dbf5d78455d4d6a41d186da50adc1122ec066f4',
  'ezyang/htmlpurifier' => 'v4.13.0@08e27c97e4c6ed02f37c5b2b20488046c8d90d75',
  'facebook/graph-sdk' => '5.7.0@2d8250638b33d73e7a87add65f47fabf91f8ad9b',
  'friendsofphp/proxy-manager-lts' => 'v1.0.5@006aa5d32f887a4db4353b13b5b5095613e0611f',
  'friendsofsymfony/jsrouting-bundle' => '2.7.0@d56600542504148bf2faa2b6bd7571a6adf6799e',
  'gedmo/doctrine-extensions' => 'v3.0.5@f956c3c4d0c0ffdc5dd879288073772e439b6c1a',
  'geoip2/geoip2' => 'v2.11.0@d01be5894a5c1a3381c58c9b1795cd07f96c30f7',
  'giggsey/libphonenumber-for-php' => '8.12.25@7d397cbd2e01e78cf79ff347e40a403dbc4c22fa',
  'giggsey/locale' => '1.9@b07f1eace8072ccc61445ad8fbd493ff9d783043',
  'imagine/imagine' => '1.2.4@d2e18be6e930ca169e4f921ef73ebfc061bf55d8',
  'kelunik/certificate' => 'v1.1.2@56542e62d51533d04d0a9713261fea546bff80f6',
  'knplabs/knp-components' => 'v3.0.2@7db2eb032591ded5809455af8a4dfdfda079041c',
  'knplabs/knp-paginator-bundle' => 'v5.6.0@a11cd180826e9475e1079b3b64c27a1c33c48917',
  'knplabs/knp-time-bundle' => 'v1.16.0@5937765753967d691ffde7ea23770a74df9b11ba',
  'laminas/laminas-code' => '4.4.0@2b0bb59ade31a045fd3ff0097dc558bb896f6596',
  'league/commonmark' => '1.6.2@7d70d2f19c84bcc16275ea47edabee24747352eb',
  'league/uri' => '6.4.0@09da64118eaf4c5d52f9923a1e6a5be1da52fd9a',
  'league/uri-interfaces' => '2.2.0@667f150e589d65d79c89ffe662e426704f84224f',
  'league/uri-parser' => '1.4.1@671548427e4c932352d9b9279fdfa345bf63fa00',
  'lexik/form-filter-bundle' => 'dev-master@5ba5755b6072e93f08b0fa532ad1448a114cc28c',
  'liip/imagine-bundle' => '2.3.1@d0819fc9b1cd4e9e16db204467b6fe1a5316a163',
  'lorenzo/pinky' => '1.0.5@2bc1a9d5696d6496df5d5682962929165a823e57',
  'maennchen/zipstream-php' => '2.1.0@c4c5803cc1f93df3d2448478ef79394a5981cc58',
  'markbaker/complex' => '2.0.3@6f724d7e04606fd8adaa4e3bb381c3e9db09c946',
  'markbaker/matrix' => '2.1.3@174395a901b5ba0925f1d790fa91bab531074b61',
  'maxmind-db/reader' => 'v1.10.1@569bd44d97d30a4ec12c7793a33004a76d4caf18',
  'maxmind/web-service-common' => 'v0.8.1@32f274051c543fc865e5a84d3a2c703913641ea8',
  'monolog/monolog' => '2.2.0@1cb1cde8e8dd0f70cc0fe51354a59acad9302084',
  'myclabs/php-enum' => '1.8.0@46cf3d8498b095bd33727b13fd5707263af99421',
  'phpdocumentor/reflection-common' => '2.2.0@1d01c49d4ed62f25aa84a747ad35d5a16924662b',
  'phpdocumentor/reflection-docblock' => '5.2.2@069a785b2141f5bcf49f3e353548dc1cce6df556',
  'phpdocumentor/type-resolver' => '1.4.0@6a467b8989322d92aa1c8bf2bebcc6e5c2ba55c0',
  'phpoffice/phpspreadsheet' => '1.18.0@418cd304e8e6b417ea79c3b29126a25dc4b1170c',
  'psr/cache' => '1.0.1@d11b50ad223250cf17b86e38383413f5a6764bf8',
  'psr/container' => '1.1.1@8622567409010282b7aeebe4bb841fe98b58dcaf',
  'psr/event-dispatcher' => '1.0.0@dbefd12671e8a14ec7f180cab83036ed26714bb0',
  'psr/http-client' => '1.0.1@2dfb5f6c5eff0e91e20e913f8c5452ed95b86621',
  'psr/http-factory' => '1.0.1@12ac7fcd07e5b077433f5f2bee95b3a771bf61be',
  'psr/http-message' => '1.0.1@f6561bf28d520154e4b0ec72be95418abe6d9363',
  'psr/link' => '1.0.0@eea8e8662d5cd3ae4517c9b864493f59fca95562',
  'psr/log' => '1.1.4@d49695b909c3b7628b6289db5479a1c204601f11',
  'psr/simple-cache' => '1.0.1@408d5eafb83c57f6365a3ca330ff23aa4a5fa39b',
  'sensio/framework-extra-bundle' => 'v5.6.1@430d14c01836b77c28092883d195a43ce413ee32',
  'sensiolabs/ansi-to-html' => 'v1.2.1@94a3145aae4733ff933c8910263ef56d1ae317a9',
  'stof/doctrine-extensions-bundle' => 'dev-master@7322d49bd7ff408e44704c0cd939889ddbb490f6',
  'swiftmailer/swiftmailer' => 'v6.2.7@15f7faf8508e04471f666633addacf54c0ab5933',
  'symfony/asset' => 'v5.2.10@c65584ca108c9e51c80cc49ebdcb47dfd995431c',
  'symfony/cache' => 'v5.2.10@aaab9c4f6eccf20906c7675be3bf826e362676ce',
  'symfony/cache-contracts' => 'v2.4.0@c0446463729b89dd4fa62e9aeecc80287323615d',
  'symfony/config' => 'v5.2.10@1156feb067e6962b3c4444d172fd0d4d8473cd5b',
  'symfony/console' => 'v5.2.10@9e18ae5de0ca8c6d0a9784f5b4ae94fad5325040',
  'symfony/css-selector' => 'v5.2.10@fcd0b29a7a0b1bb5bfbedc6231583d77fea04814',
  'symfony/dependency-injection' => 'v5.2.10@22b1ed3e5d080d69ec913e04eac3699eafb6b5b4',
  'symfony/deprecation-contracts' => 'v2.4.0@5f38c8804a9e97d23e0c8d63341088cd8a22d627',
  'symfony/doctrine-bridge' => 'v5.2.10@8911230b0861da58c42158bc8ad640f4f6658efc',
  'symfony/dotenv' => 'v5.2.10@1ac423fcc9548709077f90aca26c733cdb7e6e5c',
  'symfony/error-handler' => 'v5.2.10@5f52636e5772b21ab80fe868b54b11c3177c55c6',
  'symfony/event-dispatcher' => 'v5.2.10@2ffa4bf7469317e23cc5e3f716db6071e6525f5a',
  'symfony/event-dispatcher-contracts' => 'v2.4.0@69fee1ad2332a7cbab3aca13591953da9cdb7a11',
  'symfony/expression-language' => 'v5.2.10@e3c136ac5333b0d2ca9de9e7e3efe419362aa046',
  'symfony/filesystem' => 'v5.2.10@9aa15870b021a34de200a15cff38844db4a930fa',
  'symfony/finder' => 'v5.2.10@0ae3f047bed4edff6fd35b26a9a6bfdc92c953c6',
  'symfony/flex' => 'v1.13.3@2597d0dda8042c43eed44a9cd07236b897e427d7',
  'symfony/form' => 'v5.2.10@51f75b46f623cbf715ee9894657fe432455821c4',
  'symfony/framework-bundle' => 'v5.2.10@0f47ae9a391efdec9632275c1cb506290600af11',
  'symfony/http-client' => 'v5.2.10@a1fef5661ae36f39c1d48cb0672c4591e9452323',
  'symfony/http-client-contracts' => 'v2.4.0@7e82f6084d7cae521a75ef2cb5c9457bbda785f4',
  'symfony/http-foundation' => 'v5.2.10@a148e5d6e69562a9ec0ab3bcf35c02585114cbce',
  'symfony/http-kernel' => 'v5.2.10@91aaf791281a3f3f801a9257b27be5f9dfdb3dcf',
  'symfony/intl' => 'v5.2.10@cf40e74a9547e810747c584be216dd0c15632669',
  'symfony/mailer' => 'v5.2.10@9b4d874082b337759ce7c4ef608ecf63982a4472',
  'symfony/mime' => 'v5.2.10@0cb96ba3149255f5e5e257db9ea38ef86e154111',
  'symfony/monolog-bridge' => 'v5.2.10@065a6ce960e4beb31a30bdaeae6e6317ab4aa810',
  'symfony/monolog-bundle' => 'v3.7.0@4054b2e940a25195ae15f0a49ab0c51718922eb4',
  'symfony/notifier' => 'v5.2.10@8e68f6cd99535e76f11cde84192f95b3f9b0ed1c',
  'symfony/options-resolver' => 'v5.2.10@ceab1c225d04aceac65dae76063a0a47bdf12285',
  'symfony/orm-pack' => 'v2.1.0@357f6362067b1ebb94af321b79f8939fc9118751',
  'symfony/polyfill-intl-grapheme' => 'v1.23.0@24b72c6baa32c746a4d0840147c9715e42bb68ab',
  'symfony/polyfill-intl-icu' => 'v1.23.0@4a80a521d6176870b6445cfb469c130f9cae1dda',
  'symfony/polyfill-intl-idn' => 'v1.23.0@65bd267525e82759e7d8c4e8ceea44f398838e65',
  'symfony/polyfill-intl-normalizer' => 'v1.23.0@8590a5f561694770bdcd3f9b5c69dde6945028e8',
  'symfony/polyfill-mbstring' => 'v1.23.0@2df51500adbaebdc4c38dea4c89a2e131c45c8a1',
  'symfony/polyfill-php73' => 'v1.23.0@fba8933c384d6476ab14fb7b8526e5287ca7e010',
  'symfony/polyfill-php80' => 'v1.23.0@eca0bf41ed421bed1b57c4958bab16aa86b757d0',
  'symfony/polyfill-php81' => 'v1.23.0@e66119f3de95efc359483f810c4c3e6436279436',
  'symfony/process' => 'v5.2.10@53e36cb1c160505cdaf1ef201501669c4c317191',
  'symfony/property-access' => 'v5.2.10@ef56ae6b6c95749352cca75a0efe1b3544fa176a',
  'symfony/property-info' => 'v5.2.10@3e96404cea2f41ea65493da526245c2b4b5ebf4e',
  'symfony/proxy-manager-bridge' => 'v5.2.10@4e4997e77f30b4caed2b3cebefdecd7031e25a00',
  'symfony/requirements-checker' => 'v2.0.0@e3d5565eb69a4a2195905c8669f32e988c8e6be0',
  'symfony/routing' => 'v5.2.10@8bc1f4ac6a46f63eca345d90443a7e44908142ae',
  'symfony/security-bundle' => 'v5.2.10@642a43f0c93752df9bab473b14192053c137583b',
  'symfony/security-core' => 'v5.2.10@94225470036d67ae8bb6a746809fe737aa7c0319',
  'symfony/security-csrf' => 'v5.2.10@89f7d0778d988579e16a173cbdd9656c1bfd8b79',
  'symfony/security-guard' => 'v5.2.10@9fc67f0fd82b6f5334099d2ba1ecba048818c60f',
  'symfony/security-http' => 'v5.2.10@dc61639e1320010bd14b0c1b355643da83fd5be6',
  'symfony/serializer' => 'v5.2.10@e6b20ac94f1d0553b84cdb268600db021067727f',
  'symfony/service-contracts' => 'v2.4.0@f040a30e04b57fbcc9c6cbcf4dbaa96bd318b9bb',
  'symfony/stopwatch' => 'v5.2.10@64f4ed9232ad5372df8a61b1311502202fb0a03c',
  'symfony/string' => 'v5.2.10@abd6bb17be75ddb10b022f02820464b785882a7f',
  'symfony/swiftmailer-bundle' => 'v3.5.2@6b72355549f02823a2209180f9c035e46ca3f178',
  'symfony/templating' => 'v5.2.10@0dcfcef6c593cee370ab6945a7008b4ee5bcdea0',
  'symfony/translation' => 'v5.2.10@dc49cfb0d1d1bf6a9aaccccee570ef62e7b095c4',
  'symfony/translation-contracts' => 'v2.4.0@95c812666f3e91db75385749fe219c5e494c7f95',
  'symfony/twig-bridge' => 'v5.2.10@3590e0e352a038b23f65410f388ce1a800debc6c',
  'symfony/twig-bundle' => 'v5.2.10@c078458e37a90b4ec8abb9f8fe6c3011d35e8039',
  'symfony/validator' => 'v5.2.10@8b882f5a50df87ee194ccd8d7e4ebf5cabebad5e',
  'symfony/var-dumper' => 'v5.2.10@85bc8988d49d3f3bbede1e937f758d5dcb9dd694',
  'symfony/var-exporter' => 'v5.2.10@7a7c9dd972541f78e7815c03c0bae9f81e0e9dbb',
  'symfony/web-link' => 'v5.2.10@46285629b31ae75dcc873e2c2a558f4751ce368b',
  'symfony/webpack-encore-bundle' => 'v1.11.2@f282fb17ffa4839ba491eb7e3f5ffdd40c86f969',
  'symfony/yaml' => 'v5.2.10@cd7930d6a7e0d8ceac299846235bc6e2e032c3a3',
  'tijsverkoyen/css-to-inline-styles' => '2.2.3@b43b05cf43c1b6d849478965062b6ef73e223bb5',
  'twig/cssinliner-extra' => 'v3.3.0@89a7b0f64c1296068864d540567428210c9de340',
  'twig/extra-bundle' => 'v3.3.1@e12a8ee63387abb83fb7e4c897663bfb94ac22b6',
  'twig/inky-extra' => 'v3.3.0@36c77999814ac64617e03ce7b8092c3c4dab5e4d',
  'twig/intl-extra' => 'v3.3.0@919e8f945c30bd3efeb6a4d79722cda538116658',
  'twig/markdown-extra' => 'v3.3.1@a9fe276dbb7f837c3f4ecc6dad89bcccb9fc8bc9',
  'twig/twig' => 'v3.3.2@21578f00e83d4a82ecfa3d50752b609f13de6790',
  'webmozart/assert' => '1.10.0@6964c76c7804814a842473e0c8fd15bab0f18e25',
  'willdurand/jsonp-callback-validator' => 'v1.1.0@1a7d388bb521959e612ef50c5c7b1691b097e909',
  'doctrine/data-fixtures' => '1.5.0@51d3d4880d28951fff42a635a2389f8c63baddc5',
  'doctrine/doctrine-fixtures-bundle' => '3.4.0@870189619a7770f468ffb0b80925302e065a3b34',
  'nikic/php-parser' => 'v4.10.5@4432ba399e47c66624bc73c8c0f811e5c109576f',
  'symfony/browser-kit' => 'v5.2.10@4fdcde557ae8f4f3666a9aad9dc076448e56a31a',
  'symfony/debug-bundle' => 'v5.2.4@ec21bd26d24dab02ac40e4bec362b3f4032486e8',
  'symfony/dom-crawler' => 'v5.2.10@6854b525d68ec7ddfe5f2d57521612f995257226',
  'symfony/maker-bundle' => 'v1.31.1@4f57a44cef0b4555043160b8bf223fcde8a7a59a',
  'symfony/phpunit-bridge' => 'v5.3.0@15cab721487b7bf43ad545a1e7d0095782e26f8c',
  'symfony/profiler-pack' => 'v1.0.5@29ec66471082b4eb068db11eb4f0a48c277653f7',
  'symfony/web-profiler-bundle' => 'v5.2.10@ecff474430abe9a01a88a94960bf380bdbfa58c5',
  'paragonie/random_compat' => '2.*@',
  'symfony/polyfill-ctype' => '*@',
  'symfony/polyfill-iconv' => '*@',
  'symfony/polyfill-php72' => '*@',
  'symfony/polyfill-php71' => '*@',
  'symfony/polyfill-php70' => '*@',
  'symfony/polyfill-php56' => '*@',
  '__root__' => 'No version set (parsed as 1.0.0)@',
);

    private function __construct()
    {
    }

    /**
     * @psalm-pure
     *
     * @psalm-suppress ImpureMethodCall we know that {@see InstalledVersions} interaction does not
     *                                  cause any side effects here.
     */
    public static function rootPackageName() : string
    {
        if (!class_exists(InstalledVersions::class, false) || !(method_exists(InstalledVersions::class, 'getAllRawData') ? InstalledVersions::getAllRawData() : InstalledVersions::getRawData())) {
            return self::ROOT_PACKAGE_NAME;
        }

        return InstalledVersions::getRootPackage()['name'];
    }

    /**
     * @throws OutOfBoundsException If a version cannot be located.
     *
     * @psalm-param key-of<self::VERSIONS> $packageName
     * @psalm-pure
     *
     * @psalm-suppress ImpureMethodCall we know that {@see InstalledVersions} interaction does not
     *                                  cause any side effects here.
     */
    public static function getVersion(string $packageName): string
    {
        if (class_exists(InstalledVersions::class, false) && (method_exists(InstalledVersions::class, 'getAllRawData') ? InstalledVersions::getAllRawData() : InstalledVersions::getRawData())) {
            return InstalledVersions::getPrettyVersion($packageName)
                . '@' . InstalledVersions::getReference($packageName);
        }

        if (isset(self::VERSIONS[$packageName])) {
            return self::VERSIONS[$packageName];
        }

        throw new OutOfBoundsException(
            'Required package "' . $packageName . '" is not installed: check your ./vendor/composer/installed.json and/or ./composer.lock files'
        );
    }
}
