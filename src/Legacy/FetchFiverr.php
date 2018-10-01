<?php
/**
 * User: remmel
 * Date: 16/07/18
 * Time: 02:04
 */

namespace App\Legacy;


class FetchFiverr {
    public function invoicesId(\DateTime $date, float $amount) {

        //https://www.fiverr.com/login


        //
        //curl 'https://www.fiverr.com/users/remmel/orders/type/all' -H 'if-none-match: W/"d2c7335a5e7de900ac45a91b176cc16c"' -H 'accept-encoding: gzip, deflate, br' -H 'x-csrf-token: Kx00aLLtWiLh4mgECHWVMs+lmVaDRHjHcY0oN5syDjc=' -H 'accept-language: fr' -H 'user-agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.167 Safari/537.36' -H 'accept: text/javascript; charset=utf-8' -H 'referer: https://www.fiverr.com/users/remmel/orders/type/all' -H 'authority: www.fiverr.com' -H 'cookie: _ga=GA1.2.967107640.1531579791; __cfduid=d4754174e4a8f6f37bb064ce2e51ec2291531579793; guest_currency=EUR; u_guid=70df9138-11bd-4ae3-892d-c082eb63dce3; _fiverr_session_key=873834afbcee0347f8bf88465eda27df; visited_fiverr=true; px-abgroup=A; px-abper=100; _pxvid=9416d9b0-888a-11e8-a925-ed2026da8aae; _gid=GA1.2.565569339.1531698943; ftr_ncd=6; ftr_blst_1h=1531698944076; fbm_202127659076=base_domain=.fiverr.com; sign_up_page=yup; was_logged_in=1%3Bremymellet; new_guid=79298b12-30b2-4ccf-98eb-ab136a4f3199; hodor_creds=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJleHAiOjE1NjMyMzUwMzIsImlzcyI6ImZpdmVyci9ob2Rvci92MiIsInVpZCI6Mzk5NzAwMCwidXBzIjoiZDM5MGRkYWRlZWRjYzExNjJlOTMzYjE3Y2YzNzk4ZGE1MzQwNWE1YjI2MjI2ZTFmOTA3NTgzNTc5MDMwMDg2MTVlNGE1YzA4YmE4ZjJlOGZkMjQ0Y2Q0N2YwYWUwMThiYWJmZTg1MjdhMWRlN2IwMGRhN2M2MDE3MGJhNzc4ZDAiLCJpYXQiOjE1MzE2OTkwMzIsInBzdCI6IjRkNDQ3Y2JhNzVjNGExMjRhMDk0YjU2ZGIxMDBlOWRjIn0.10LSrs871YXf2x5baVPkNy0wFnN_Ss4_ZlKvV1BXxmg; locale_login=true; logged_in_currency=USD; _pxff_tm=1; ab.storage.userId.df88c069-84bb-4ae8-9830-0cfe6e341181=%7B%22g%22%3A%22remmel%22%2C%22c%22%3A1531699035836%2C%22l%22%3A1531699035836%7D; last_content_pages_=users%7C%7C%7Cshow%7C%7C%7Cremmel%3B; fbsr_202127659076=Hc0UWfoIfM7M8rCHyMQxBKfxjDX5EpxOjaK1tycjP1U.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImNvZGUiOiJBUUJaS0Y5Q0t2OU8wQnZJQnBiNXBHTVJYc292bzQ2VF9GMkNTakZMeW9XQlFfRExmSjJIMFM4OU9naFFtMFFxMFVKeFczOXU5SWhVSWl6WVdoUlZ2U081bTJwUDlfMktuTTRYZlB6MnN2SXpkY1BLXzdxVDFTUVJobEd2ZlNVRkVfMzN1YUo1YU5oVk9Ka01Gc3Fla0RRNmpKeXhuTTRibFF5Tk5LbWZvaUxVNlpwcnVuNEdvbGFvcnhKajJxY21hRUhMNGh1cko0cjI2aVhVZm9xbFJYWDJHck9mSVM3SHo5Unk1MzNwSE1TQjFMX3FVVTRXZ1paaEg0QlhiS1ZuZ19zYzF4dTVmdS1OVkkwMFhtenJqZWxObHhPWWFHekR3eld1c05wME9NS05fYVh5WmpYcEg0UHlyR252bF9Tdk9OLXA2cVdrdGJRdl82WkhRMEExNjVfYiIsImlzc3VlZF9hdCI6MTUzMTY5OTE4MSwidXNlcl9pZCI6IjEwMTU2MjIyNDcwNTgwMTEzIn0; linkedin_oauth_778mnh7r4u6qio=null; linkedin_oauth_778mnh7r4u6qio_crc=null; ki_r=; ki_t=1531699234748%3B1531699234748%3B1531699319685%3B1%3B3; _dc_gtm_UA-12078752-1=1; pv_monthly=19%3B17%3B19; _uetsid=_uet84c874a1; _px=3th61tRBKDozvcgtkoEeOKmC49E3XYUv8OMvKKsyDOAU8JYuGsLXVZdvCjKZW79dx3ws3B2/FztD3z0isgaY2A==:1000:nYzaK+ZBWPHIYjCP6PZ5Jubrh+RoGE+8TOb9e2yzXDQ+rk23EJtuD5DZQ3WL0KA57Sq8Fo880fL+EOFOfAH3q/Jcb+amnlfRPzuwP+VA9mAwPW3vJlVz/L7dC9VZesFtoK8fOnerQLwYaqD/6D1dbOvvFN83MYkBuHeIvv+MoiS108a76VhcDetjA6IoSV1tbyZzVxWLXecKCdRj3Dedrr4Ruw6GmMQoPZbYNkZqLnHfQw2eDndK9SCKyOxkYrDxV0L+2+HQU7dFlarhV5JmgA==; _ceg.s=pbxmu5; _ceg.u=pbxmu5; ab.storage.sessionId.df88c069-84bb-4ae8-9830-0cfe6e341181=%7B%22g%22%3A%2224e69239-829c-0715-4cc5-0a2279cf7b67%22%2C%22e%22%3A1531701222446%2C%22c%22%3A1531699035839%2C%22l%22%3A1531699422446%7D; forterToken=dcc9b418e90f4003b5b8ac88990d9fb0_1531699422390__UDF43_6; mp_436ab54ce79a37742241d4f156f647e9_mixpanel=%7B%22distinct_id%22%3A%203997000%2C%22%24initial_referrer%22%3A%20%22%24direct%22%2C%22%24initial_referring_domain%22%3A%20%22%24direct%22%7D; _pxde=c972dfa9f06310f063ab23e4f32dfa3bd649a97c630539da1f56cea75b5f1dd7:eyJ0aW1lc3RhbXAiOjE1MzE2OTk0MjQ4MDQsImlwY19pZCI6W119' --compressed
    }

}