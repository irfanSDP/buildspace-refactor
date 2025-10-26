<?php
/**
 * SAML 2.0 remote SP metadata for SimpleSAMLphp.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-sp-remote
 */

$metadata['https://' . getenv('SAMLAUTH_HOST')] = array (
  'SingleLogoutService' =>
  array (
    0 =>
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
      'Location' => 'https://' . getenv('SAMLAUTH_HOST') . '/module.php/saml/sp/saml2-logout.php/buildspace-sp',
    ),
    1 =>
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
      'Location' => 'https://' . getenv('SAMLAUTH_HOST') . '/module.php/saml/sp/saml2-logout.php/buildspace-sp',
    ),
  ),
  'AssertionConsumerService' =>
  array (
    0 =>
    array (
      'index' => 0,
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
      'Location' => 'https://' . getenv('SAMLAUTH_HOST') . '/module.php/saml/sp/saml2-acs.php/buildspace-sp',
    ),
    1 =>
    array (
      'index' => 1,
      'Binding' => 'urn:oasis:names:tc:SAML:1.0:profiles:browser-post',
      'Location' => 'https://' . getenv('SAMLAUTH_HOST') . '/module.php/saml/sp/saml1-acs.php/buildspace-sp',
    ),
    2 =>
    array (
      'index' => 2,
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact',
      'Location' => 'https://' . getenv('SAMLAUTH_HOST') . '/module.php/saml/sp/saml2-acs.php/buildspace-sp',
    ),
    3 =>
    array (
      'index' => 3,
      'Binding' => 'urn:oasis:names:tc:SAML:1.0:profiles:artifact-01',
      'Location' => 'https://' . getenv('SAMLAUTH_HOST') . '/module.php/saml/sp/saml1-acs.php/buildspace-sp/artifact',
    ),
  ),
);

/*
 * Example SimpleSAMLphp SAML 2.0 SP
 */
$metadata['https://saml2sp.example.org'] = array(
    'AssertionConsumerService' => 'https://saml2sp.example.org/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp',
    'SingleLogoutService' => 'https://saml2sp.example.org/simplesaml/module.php/saml/sp/saml2-logout.php/default-sp',
);

/*
 * This example shows an example config that works with G Suite (Google Apps) for education.
 * What is important is that you have an attribute in your IdP that maps to the local part of the email address
 * at G Suite. In example, if your Google account is foo.com, and you have a user that has an email john@foo.com, then you
 * must set the simplesaml.nameidattribute to be the name of an attribute that for this user has the value of 'john'.
 */
$metadata['google.com'] = array(
    'AssertionConsumerService' => 'https://www.google.com/a/g.feide.no/acs',
    'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
    'simplesaml.nameidattribute' => 'uid',
    'simplesaml.attributes' => FALSE,
);

$metadata['https://legacy.example.edu'] = array(
    'AssertionConsumerService' => 'https://legacy.example.edu/saml/acs',
        /*
         * Currently, SimpleSAMLphp defaults to the SHA-256 hashing algorithm.
     * Uncomment the following option to use SHA-1 for signatures directed
     * at this specific service provider if it does not support SHA-256 yet.
         *
         * WARNING: SHA-1 is disallowed starting January the 1st, 2014.
         * Please refer to the following document for more information:
         * http://csrc.nist.gov/publications/nistpubs/800-131A/sp800-131A.pdf
         */
        //'signature.algorithm' => 'http://www.w3.org/2000/09/xmldsig#rsa-sha1',
);

$metadata['buildspace-sp'] = [
    'AssertionConsumerService' => 'https://bq.buildspace.local/saml/acs',
    'SingleLogoutService'      => 'https://bq.buildspace.local/saml/logout',
];

