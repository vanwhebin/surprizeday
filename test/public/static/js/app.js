let indexUrl, moreUrl, joinUrl, tokenUrl, tokenEmailUrl, claimUrl, latestUrl, accountUrl, expireUrl, oauthLoginUrl,
    raffleUrl, dealsUrl;
let token, id;
let gravatarUrl = "http://www.gravatar.com/avatar/{$avatarHash}?s=256&d=robohash";
token = $('meta[name="X-TOKEN"]').attr('content') ? $('meta[name="X-TOKEN"]').attr('content'): getCookie('token');
id = $('#activity').data('activity_id');
let landingPage = "/landing";
const version = '/api/v1/';
latestUrl =  version + 'activity/latest';
indexUrl = version + 'activity/' + id ;
tokenUrl = version + 'token/user';
tokenEmailUrl = version + 'token/userEmail';
joinUrl = version + 'activity/participant';
claimUrl = version + 'activity/'+ id +'/claim';
accountUrl = version + 'activity/account';
expireUrl = version + 'activity/expired';
dealsUrl = version + 'deals/latest';
oauthLoginUrl = version + 'login';
raffleUrl = '/raffle/' + id;


const Toast = Swal.mixin({
    toast: true,
    position: 'top',
    showConfirmButton: false,
    timer: 1500
});


function loginIntoApp(accessToken, callback) {
    FB.api('/me', {fields: "picture,email,name,id"}, function (response) {
        var enterDrawBtn = $('.users-box .not-joined button');
        enterDrawBtn.data('avatar', response.picture.data.url);
        enterDrawBtn.data('name', response.name);

        var data = {
            accessToken: accessToken,
            userID: response.id,
            name: response.name,
            email: response.email,
            from: 1,
            avatar: response.picture.data.url
        };
        callback(data);
    });
}

/**
 * data为用户信息
 * async  默认为异步请求, true
 * @param data
 */
function getToken(data){
    $.ajax({
        url: tokenUrl,
        data: data,
        dataType: 'JSON',
        type: 'POST',
        success: function(res){
            saveToken(res.token);
            if (getCookie('entered')) {
                enterAfterLogin();
            }
        }
    });
}


/**
 *  首页节流函数
 * 指定时间间隔内只会执行一次任务；
 * @param fn
 * @param interval
 * @returns {Function}
 */
function throttle(fn, interval=300) {
  let run = true;
  return function(){
      if (!run) return;
      run = false;
      setTimeout(() => {
         fn.apply(this, arguments);
         run = true;
      }, interval);
  }
}

/**
 * 任务频繁触发的情况下，只有任务触发的间隔超过指定间隔的时候，任务才会执行。
 * *首页防抖函数
 * @param fn
 * @param interval
 * @returns {Function}
 */
function debounce(fn, interval=300) {
    let timeout = null;
    return function(){
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            fn.apply(this, arguments);
        }, interval);
    }
}

function homeMore() {
    let pageHeight = $('body').height(),
        scrollTop = $(window).scrollTop(),
        winHeight = $(window).height(),
        thresold = pageHeight - scrollTop - winHeight;
    let page;
    var giveawayCon = $('#giveaway-container');
    var dealsCon = $('#deals-container');
    if (thresold > -100 && thresold <= 20) {
        let selected = $('.nav-tabs li .active').attr('href');
        if (selected === '#giveaways' &&　!giveawayCon.data('last')) {
            page = giveawayCon.data('curpage')  ? parseInt(giveawayCon.data('curpage')) : 1;
            ajaxRequest('GET', latestUrl, {"num": 15, "page": (page + 1)}, list);
        } else if (selected === '#deals'　&&　!dealsCon.data('last')) {
            page = dealsCon.data('curpage')  ? parseInt(dealsCon.data('curpage')) : 1;
            ajaxRequest('GET', dealsUrl, {"num": 15, "page": (page + 1)}, dealList);
        }
    } else {
        $('.loading').fadeOut();
    }
}

function saveToken(token) {
    $('meta[name="X-TOKEN"]').attr('content', token);
    setTokenHeader(token);
    setCookie('token', token, (30*60*1000), '/');
}

function setCookie(k, v, microSec, p){
    var key, path, expired;
    if (microSec) {
        var exp = new Date();
        var currTime = exp.getTime();
        exp.setTime(currTime + microSec);
        expired = 'expires=' + exp.toUTCString() +';';
    } else {
        expired = "";
    }
    key = k + '=' + v + ';';
    path = 'path='+ p + ';';
    document.cookie = key + path + expired;
}


function getCookie(name) {
    var cookieValue = null;
    if (document.cookie && document.cookie !== '') {
        var cookies = document.cookie.split(';');
        for (var i = 0; i < cookies.length; i++) {
            var cookie = $.trim(cookies[i]);
            if (cookie.substring(0, name.length + 1) === (name + '=')) {
                cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                break;
            }
        }
    }
    return cookieValue;
}

function setTokenHeader(token) {
    $.ajaxSetup({
        beforeSend: function(xhr, settings) {
            xhr.setRequestHeader("token", token);
        }
    });
}

function ajaxRequest(method, url, data, sCallback, eCallback) {
    $('.loading').fadeIn();
    $.ajax({
        type: method,
        dataType: 'JSON',
        url: url,
        data: data,
        success: function(res) {
            sCallback && sCallback(res);
        },
        error: function(err) {
            console.log(err);
            eCallback && eCallback(err);
            Swal.fire(
                'Error',
                "Error occurred, please try later",
                // err.responseJSON.msg,
                'error'
            );
        },
        complete: function () {
            $('.loading').fadeOut();
        }
    });
}

function share() {
    var shareUrl = "https://www.facebook.com/dialog/share";
    var url = window.location.href;
    var appID = 701245580337669;

    window.location.href = shareUrl + "?app_id="+ appID +"&display=popup&href= "+ url +"&redirect_uri=" + url;
}

/*home  start--------------*/

function list(activity) {
    var _con = $("#giveaway-container");
    if (activity.data.length !== 0) {
        if (parseInt(_con.data('curpage')) < parseInt(activity.current_page)) {
            append(_con, activity.data, $('#giveTemplate').html(), 1);
            _con.data('curpage',(parseInt(activity.current_page)));
        }
    } else {
        _con.data('last', true);
    }
}

function dealList(deals) {
    var _cont= $("#deals-container");
    if (deals.data.length !== 0) {
        console.log(_cont.data('curpage'));
        if (parseInt(_cont.data('curpage')) < parseInt(deals.current_page)) {
            append(_cont, deals.data, $('#dealsTemplate').html() , 2);
            _cont.data('curpage',(parseInt(deals.current_page)));
        }
    } else {
        _cont.data('last', true);
    }
}

/*-----------freebie &&　deals start--------------*/
function checkSurprizeDeals(obj, discount, code, url){
    Swal.fire({
        title: discount  + ' off code: ' + code,
        type: 'success',
        showCloseButton: true,
        confirmButtonColor: '#ff8f17',
        footer: "<div style='margin: auto'><h6><a href='https://m.me/surprizeday' style='text-decoration: underline'>Send us</a> your order number</h6><h6 style='text-align: center'>to claim $20 rebate!</h6></div>",
        confirmButtonText: 'Copy code and go'
    }).then((result) => {
        if (result.value) {
            copyCoupon(obj);
            Toast.fire({
                type: 'success',
                title: 'Code has been copied!'
            }).then(()=>{
                window.open(url);
                Swal.fire({
                    title: "Thank You!",
                    text: 'PM us with your order number and screenshot.',
                    type: 'success',
                    confirmButtonColor: '#ff8f17',
                    confirmButtonText: 'Claim rebate'
                }).then((result) => {
                    if (result.value) {
                        window.location.href = 'https://m.me/surprizeday';
                    }
                });
            });
        }
    });
}

function checkOtherDeals(obj, discount, code, url, save) {
    let title, btnText;
    if (!code) {
        title =  "You've saved " + save + '!';
        btnText = 'Shop now';
    } else {
        title = discount  + ' off code: ' + code;
        btnText = 'Copy code and Go to Amazon';
    }

    Swal.fire({
        title: title,
        type: 'success',
        showCloseButton: true,
        confirmButtonColor: '#ff8f17',
        footer: "Note: this item is not eligible for rebate.",
        confirmButtonText: btnText
    }).then((result) => {
        if (result.value) {
            copyCoupon(obj);
            window.open(url);
        }
    });
}

function clickDeal(obj){
    const save = $(obj).data('save');
    const sponsor = isNaN(parseInt($(obj).data('sponsor'))) ? 0 : parseInt($(obj).data('sponsor'));
    const discount = $(obj).data('discount');
    const code = $(obj).data('code');
    const url = $(obj).data('href');
    if (sponsor) {
        checkSurprizeDeals(obj, discount, code, url);
    } else {
        checkOtherDeals(obj, discount, code, url, save)
    }
}

/*-----------freebie && deals end--------------*/

/**-------拖动加载其他活动信息------------**/
/*文档高度*/
function getDocumentTop() {
    var scrollTop = 0, bodyScrollTop = 0, documentScrollTop = 0;
    if (document.body) {
        bodyScrollTop = document.body.scrollTop;
    }
    if (document.documentElement) {
        documentScrollTop = document.documentElement.scrollTop;
    }
    scrollTop = (bodyScrollTop - documentScrollTop > 0) ? bodyScrollTop : documentScrollTop;
    return scrollTop;
}
/*可视窗口高度*/
function getWindowHeight() {
    var windowHeight = 0;
    if (document.compatMode === "CSS1Compat") {
        windowHeight = document.documentElement.clientHeight;
    } else {
        windowHeight = document.body.clientHeight;
    }
    return windowHeight;
}
/*滚动条滚动高度*/
function getScrollHeight() {
    var scrollHeight = 0, bodyScrollHeight = 0, documentScrollHeight = 0;
    if (document.body) {
        bodyScrollHeight = document.body.scrollHeight;
    }
    if (document.documentElement) {
        documentScrollHeight = document.documentElement.scrollHeight;
    }
    scrollHeight = (bodyScrollHeight - documentScrollHeight > 0) ? bodyScrollHeight : documentScrollHeight;
    return scrollHeight;
}

function formatActivityContent(activityData, tpl){
    var html, itemtpl, tag, engaged, img;
    html = '';
    $.each(activityData, function (i, item) {
        itemtpl = tpl;
        tag = item.tag ? item.tag: '';
        engaged = item.engaged?'<label class="enrolled-label mb-0"></label>':'';
        img = item.thumb ? item.thumb.url: '/static/img/holder.png';
        itemtpl = itemtpl.replace(/\{time\}/g, item.start_time);
        itemtpl = itemtpl.replace(/\{link\}/g, item.url);
        itemtpl = itemtpl.replace(/\{image\}/g, img);
        itemtpl = itemtpl.replace(/\{name\}/g, item.title);
        itemtpl = itemtpl.replace(/\{in\}/g, engaged);
        itemtpl = itemtpl.replace(/\{tag\}/g, tag);
        itemtpl = itemtpl.replace(/\{sponsor\}/g, item.sponsor.name);
        if (!engaged) {
            itemtpl = itemtpl.replace(/tagItem/g, tag);
        }
        html += itemtpl;
    });
    return html;
}

function formatDealsContent(dealsData, tpl){
    let html, itemtpl, img, tag, bonus;
    html = tag = sponsor = '';
    $.each(dealsData, function (i, item) {
        itemtpl = tpl;
        tag = item.sponsor ?  item.rebate : '';
        bonus = item.sponsor ?  'Bonus Offer: Get $20 cash or gift card rebate!' :'';
        img = item.prize ? item.prize.main_img_url: '/static/img/holder.png';
        itemtpl = itemtpl.replace(/\{time\}/g, item.start_time);
        itemtpl = itemtpl.replace(/\{amazon_url\}/g, item.amazon_url);
        itemtpl = itemtpl.replace(/\{amazon_code\}/g, item.amazon_code);
        itemtpl = itemtpl.replace(/\{discount\}/g, item.discount);
        itemtpl = itemtpl.replace(/\{image\}/g, img);
        itemtpl = itemtpl.replace(/\{name\}/g, item.name);
        itemtpl = itemtpl.replace(/\{tag\}/g, tag + ' REBATE');
        itemtpl = itemtpl.replace(/\{save\}/g, item.discount + ' OFF');
        itemtpl = itemtpl.replace(/\{rebate\}/g, item.rebate);
        itemtpl = itemtpl.replace(/\{sponsor\}/g, item.sponsor);
        itemtpl = itemtpl.replace(/\{bonus\}/g, bonus);
        itemtpl = itemtpl.replace(/\{oldPrice\}/g, item.amazon_price);
        itemtpl = itemtpl.replace(/\{newPrice\}/g, item.promote_price);
        html += itemtpl;
    });
    return html;
}

/**
 * 首页进行加载数据
 * @param container object 装载数据的容器，比如giveaway和deals的Dom节点jq对象
 * @param data array 后台传来的数据
 * @param tpl string html模板
 // * @param page integer 分页加载的页码
 * @param type integer 加载类型 1 为giveaway 2 为deals
 */
function append(container, data, tpl, type) {
    var html = '';
    // if (page === 1) {
    //     container.html(html);
    // } else {
        if (type === 1) {
            html = formatActivityContent(data, tpl);
        } else if (type === 2) {
            html = formatDealsContent(data, tpl);
        }
        container.append(html);
    // }

    $('.lazy').Lazy();
}

/**------拖动加载其他活动信息end--------------**/
/*-----------------------home end--------*/
/*index start ---------------*/
function participate(data){
    var activity_id = $('#enterDrawBtn').data('activity_id');
    $.post(tokenUrl, data, function (res) {
        console.log(res);
        $('meta[name="X-TOKEN"]').attr('content', res.token);
        setTokenHeader(res.token);
        setCookie('token', token, 7200000, '/');
        ajaxRequest('POST', joinUrl, {activity_id: activity_id}, tryIn);
    });
}

function loginIntoFB() {
    setCookie('entered', 1, (60 * 1000), window.location.pathname);
    setCookie('loginType', 1, (30*60*1000), '/');
    window.location.href = oauthLoginUrl;
}

function loginIntoEmail(obj){
    var name = $('#recipient-name').val().trim();
    var nickname = $('#recipient-nickname').val().trim();
    var email = $('#recipient-email').val().trim();
    var gender = $("input[name='recipient-gender']:checked").val().trim();
    var data =  {name: name, nickname: nickname, email: email, gender: gender, from: 2};

    if (!name || !nickname || !email ) {
        return false;
    }
    if (!email.match(/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/ig)) {
        return false;
    }
    $(obj).attr('disabled', true);
    setTimeout(function(){ $(obj).attr('disabled', false);  }, 3000);
    ajaxRequest('POST', tokenEmailUrl, data, function(res){
        if (res.token) {
            $('#loginInModal').modal('hide');
            $('#emailLoginForm')[0].reset();
            saveToken(res.token);
            setCookie('loginType', 2, (30*60*1000), '/');
            enterAfterLogin();
        } else {
            Swal.fire("Error", "Sorry, please try later.", 'error');
            return false;
        }
    })
}


function enterAfterLogin() {
    var enterBtn = $('#enterDrawBtn');
    var activity_id = enterBtn.data('activity_id');
    join(enterBtn[0]);
    $('.not-joined').hide();
    $('.joined').show();
    var loginType = getCookie('loginType');

    if (parseInt(loginType) === 1) {
        ajaxRequest('POST', joinUrl, {activity_id: activity_id}, tryIn);
        /*clear user entered cookie*/
        setCookie('entered', '', -1 , window.location.pathname);
    } else if (parseInt(loginType) === 2) {
        ajaxRequest('POST', joinUrl, {activity_id: activity_id}, function(res){
            if (res === 'ok') {
                Swal.fire({
                    title: "You're in!",
                    html: "<span style='text-decoration: underline;cursor: pointer' onclick='updateEmailModal()'>Keep me updated on the result</span>",
                    type: 'success',
                    // backdrop:` rgba(2,2,14,0.44) url("/static/img/nyan-cat.gif") center top no-repeat`,
                    confirmButtonColor: '#ff8f17',
                    confirmButtonText: 'More Giveaways'
                }).then((result) => {
                    if (result.value) {
                        window.location.href = '/';
                    }
                });
                return true;
            } else {
                Swal.fire("Error", "Sorry, please try later.", 'error');
                return false;
            }
        });
    } else {
        console.log(loginType);
        return false;
    }
}

function genAvatar(avatar){
    if (avatar){
        avatar = avatar.indexOf('http://') !== -1 ? avatar : gravatarUrl.replace('{$avatarHash}', avatar);
    } else {
        avatar = gravatarUrl.replace('{$avatarHash}', (new Date().getTime()));
    }
    return avatar;
}

function join(obj) {
    var userCount = $('.users-box .users-count');
    var count = parseInt(userCount.text());
    var avatar = $(obj).data('avatar');

    var participantLiObj = $('.participantes li');
    var userTpl = `<li><img class="w-100" src="{avatar}" alt="{name}" onerror="this.src='/static/img/default-avatar.jpg'"></li>`;
    userCount.text(count + 1);
    participantLiObj.each(function(index, item){
        if ($(item).find('img').attr('src') === avatar) {
            $(item).remove();
        }
    });
    var str = userTpl.replace(/\{avatar\}/, avatar).replace(/\{name\}/, $(obj).data('name'));
    $('.users-box .participantes').prepend(str);
    $('.participantes li:nth-child(7)').remove()
}

function joinWithMsg(res){
    var btnObj = $('#enterDrawBtn');
    saveToken(res.token);
    btnObj.data('avatar', res.user.avatar).data('name', res.user.name);
    join(btnObj[0]);
    $('.not-joined').hide();
    $('.joined').show();
    Swal.fire(
        "You're almost there!",
        "Confirm your entry in messenger and get notified of the draw result.",
        'success'
    );
}


function tryIn(res){
    var loginType = parseInt(getCookie('loginType'));
    console.log(getCookie('loginType'));
    console.log(loginType);
    if (loginType === 1 && !getCookie('getStarted')) {
        $('#optInModal').modal('show');
        return true;
    // } else if (loginType === 2) {
    } else {
        Swal.fire({
            title: "You're in!",
            html: "<span style='text-decoration: underline;cursor: pointer' onclick='updateEmailModal()'>Keep me updated on the result</span>",
            type: 'success',
            // backdrop:` rgba(2,2,14,0.44) url("/static/img/nyan-cat.gif") center top no-repeat`,
            confirmButtonColor: '#ff8f17',
            confirmButtonText: 'More Giveaways'
        }).then((result) => {
            if (result.value) {
                window.location.href = '/';
            }
        });
        return true;
    // } else {
    //     return false;
    }
}

function updateEmailModal(){
    Swal.close();
    $('#optInModal').modal('show');
}


function optIn(obj){
    var activity_id = $(obj).data('activity_id');
    var data = {activity_id: activity_id};
    var message = $(obj).data('message');
    var avatar = $(obj).data('avatar');
    var name = $(obj).data('name');
    var xTokenObj = $('meta[name="X-TOKEN"]');
    var genToken = xTokenObj.attr('content')? xTokenObj.attr('content'): getCookie('token');
    var refer_user = getCookie('refer-user-'+ activity_id);
    var refer_type = getCookie('refer-user-type');
    /*用户是否登录*/
    if (!genToken && !avatar && !name) {
        $('#loginInModal').modal('show');
        return false;
    }

    if (refer_user) {
        data.refer = {refer_user: refer_user, refer_type:refer_type}
    }

    join(obj);
    $('.not-joined').hide();
    $('.joined').show();

    ajaxRequest('POST', joinUrl, data, tryIn);
}

function winnersFormat(winners) {
    var winnerTpl, winnersHtml, winnersCount;
    winnersCount = winners.length;
    winnerTpl = `<li class="col-sm-4"><img data-src="{image}" src="/static/img/default-avatar.jpg" class="lazy"><p>{name}</p></li>`;
    winnersHtml = '';
    for (var k=0; k < winnersCount; k++) {
        var info = winners[k].info;
        winnersHtml += winnerTpl.replace(/\{image\}/g, info.avatar).replace(/\{name\}/g, info.name);
    }

    return winnersHtml;
}

function promoCodeFormat(codeData, curUserID){
    let codeHtml = '';

    for(let i = 0;i< codeData.length;i++){
        let codeTpl = `
    <div class="discount-code mt-3 mb-4 text-left">
        <p class="h6 font-weight-normal">{summary}</p>
        <ul class="text-white p-3 color">
            <h2 class="pt-2">{desc}</h2>
            <small>{name}</small>
            <button type="button" class="btn font-weight-normal bg-white pt-2 copy-coupon" data-code="{code}" onclick="copyCoupon(this)">COPY</button>
        </ul>
        <a href="{url}" target="_blank"><button type="button" class="btn w-100 bg-gradient">Redeem on {platform}</button></a>
    </div>
    `;
        let url = landingPage + '?url=' + codeData[i].url + "&userID=" + curUserID;
        codeTpl = codeTpl.replace(/\{summary\}/g, codeData[i].summary).replace(/\{desc\}/g, codeData[i].desc);
        codeTpl = codeTpl.replace(/\{name\}/g, codeData[i].name).replace(/\{code\}/g, codeData[i].code.code);
        codeTpl = codeTpl.replace(/\{url\}/g, url).replace(/\{platform\}/g, codeData[i].code.platform);

        codeHtml += codeTpl;
    }

    return codeHtml;
}

function copyCoupon(obj) {
    let text = $(obj).data('code');
    if (window.clipboardData) { // Internet Explorer
        window.clipboardData.setData("Text", text);
    } else {
        const input = document.createElement("input");
        input.setAttribute('readonly', 'readonly');
        input.setAttribute('value', text);
        document.body.appendChild(input);
        input.focus();
        input.setSelectionRange(0, input.value.length);
        input.select();
        document.execCommand('selectAll');
        try {
            document.execCommand('copy')
        } catch (err) {
            console.log('Oops, unable to copy');
        }
        Toast.fire({
            icon: 'success',
            title: 'Code has been copied!'
        });
        // $(obj).text('COPIED');
        document.body.removeChild(input);
    }
}

/**
 *
 * @param activity  活动信息
 * @param engaged   当前用户参与信息 | null
 * @param userInfo  用户参与活动信息 是否中奖，中奖者有哪些
 * @param moreInfo  当前活动的推荐活动或者是奖券
 * @returns {boolean}
 */
function modalFormat(activity, engaged, userInfo, moreInfo){
    let recommend = moreInfo.recommend;
    let winnerModal = $('#winnerModal');
    let loseModal = $('#loseModal');
    let winnerAction = $('#result-action');
    let loserAction = $('#result-lose');
    let overModal = $('#overModal');
    let winnerHtml = winnersFormat(userInfo.winners);
    let winnerListUrl = '/winners';
    let level = '';
    $('.lazy').lazy();

    if (!userInfo.checked) {
        if (!engaged) {
            /*用户未参与*/
            if (!getCookie('showedResult')) {
                /*当用户已经查看过结果*/
                overModal.find('.over-recommend-title').text(recommend.title);
                overModal.find('.over-recommend-pic').attr('src',recommend.thumb.url);
                overModal.find('.over-recommend-time').text(recommend.start_time);
                overModal.find('.over-recommend-link').attr('href', '/surprize/' + recommend.slug);
                overModal.modal('show');
                $('.guide-msg').show();
                setCookie('showedResult', 1, null, window.location.pathname);
            }
            return false;
        }
        /*已参与用戶第一次查看, 要起弹窗*/
        if (userInfo.winner) {
            /*winner*/
            var img = userInfo.winner.info.avatar ?
                userInfo.winner.info.avatar : '/static/img/default-avatar.jpg';
            winnerModal.find('.avatar').attr('src', img);
            winnerModal.find('.username').text(userInfo.winner.info.name);
            winnerModal.find('.recommendBox .recommend-winner-img').attr('src', recommend.thumb.url);
            winnerModal.find('.recommendBox .recommend-winner-title').text(recommend.title);
            winnerModal.find('.recommendBox .recommend-winner-url').attr('href', '/surprize/' + recommend.slug);
            if (userInfo.winner.level > 1) {
                if (userInfo.winner.level === 2) {
                    level = '(2nd Place)';
                }
                winnerModal.find('.place-class').text(level);
                winnerAction.before(promoCodeFormat(moreInfo.sale, engaged.user_id));
                winnerAction.hide();
            }
            winnerModal.find('.place').text(level);
            winnerModal.modal('show');
        } else {
            /*loser*/
            if (moreInfo.sale && moreInfo.sale.length) {
                loserAction.before(promoCodeFormat(moreInfo.sale, engaged.user_id));
            }

            loseModal.find('.winner-list').html(winnerHtml);
            loseModal.find('.current-prize-title').html(activity.title);
            loseModal.find('.winners-list-link').attr('href', winnerListUrl);
            loseModal.find('.lose-recommend-pic').attr('src', recommend.thumb.url);
            loseModal.find('.lose-recommend-title').text(recommend.title);
            loseModal.find('.lose-recommend-time').text(recommend.start_time);
            loseModal.find('.lose-recommend-url').attr('href', '/surprize/' + recommend.slug);
            loseModal.modal('show');
        }
    } else {
        /*只显示最终开奖已结束的情况*/
        if (!getCookie('showedResult')) {
            /*当用户已经查看过结果*/
            overModal.find('.over-recommend-pic').attr('src', recommend.thumb.url);
            overModal.find('.over-recommend-title').text(recommend.title);
            overModal.find('.over-recommend-time').text(recommend.start_time);
            overModal.find('.over-recommend-link').attr('href', '/surprize/' + recommend.slug);
            overModal.modal('show');
            setCookie('showedResult', 1, null, window.location.pathname);
        }
    }
}

function usersFormat(users) {
    var userHtml, usersCount, userTpl;
    usersCount = users.data.length;
    userTpl = `<li><i role="img" class="lazyload-avatar"><i><img class="w-100 lazy" data-src="{avatar}" ></i></i></li>`;
    userHtml = '';
    if (usersCount > 0 && users.data[0].user && users.data[0].user !== undefined ) {
        for (var k = 0; k < usersCount; k++) {
            var avatar = users.data[k].user.avatar ? users.data[k].user.avatar : '/static/img/default-avatar.jpg';
            userHtml += userTpl.replace(/\{avatar\}/g, avatar);
        }
        $('.participantes').html(userHtml);
        $('.lazy').lazy();
    }
    $('.users-count').text(users.total);
}

function mediaFormat(prize) {
    var swiperHtml, picTpl, vidTpl, picLen;
    picLen = prize.pic.length;
    swiperHtml = '';
    picTpl = `<div class="swiper-slide"><img class="w-100" src="{img}" /></div>`;
    vidTpl = `<div class="swiper-slide"><video width="100%" height="auto" controls><source src="{video}" type="video/mp4"></video></div>`;
    for (var i = 0; i < picLen; i++) {
        swiperHtml += picTpl.replace(/\{img\}/g, prize.pic[i]);
    }
    if (prize.video) {
        swiperHtml += vidTpl.replace(/\{video\}/g, prize.video);
    }
    $('.activity_media').html(swiperHtml);
}

function activityFormat(activity) {
    mediaFormat(activity.prize);
    $('.activity_title').text(activity.title);
    $('.activity_time').text(dateFormat(activity.start_time));
    $('#prize-desc').html(activity.description);
}

function dateFormat(timeStamp) {
    var date = new Date(timeStamp * 1000);
    var Y = date.getFullYear();
    var M = (date.getMonth() + 1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1);
    var D = date.getDate();
    // var h = date.getHours() > 12 ? (date.getHours() - 12) + ' pm': date.getHours() + ' am';
    return (M + '/' + D + '/' +Y);
}

function slide() {
    var picsSwiper = new Swiper('#pics-swiper', {
        autoHeight: true,
        observer: true,
        loop: true,
        autoPlay: true,
        pagination: {
            el: '#pics-swiper .swiper-pagination',
            type: 'fraction'
        },
    });

    var n = $(".swiper-slide").length;
    $(".slide-to .pic").click(function () {
        picsSwiper.slideTo(0);
    });
    $(".slide-to .video").click(function () {
        picsSwiper.slideTo(n - 1);
    })
}

function handler(res) {
    var users, activity, info, usersBox, winnerBox, entry ;
    users = res.users;
    activity = res.activity;
    info = res.info;
    usersBox = $('.users-box');
    winnerBox = $('.winners-box');
    slide();
    if (info !== undefined) {
        /*活动已经结束*/
        winnerBox.show();
        winnerBox.find('.winners-list .winners').html(winnersFormat(info.userInfo.winners));
        modalFormat(activity, users.curUser, info.userInfo, info.moreInfo);
    } else {
        usersBox.show();
        usersFormat(users.users);
        if (users.curUser && users.curUser.user !== undefined) {
            /*当前用户参与了活动*/
            if (users.entry > 1) {
                $('#entryNum').html("<br><strong>"+ users.entry + " Entries</strong>");
            }
            usersBox.find('.joined').show();
        } else {
            /*当前用户尚未参与活动*/
            if (users.cur) {
                usersBox.find('.not-joined button').data('name', users.cur.name);
                usersBox.find('.not-joined button').data('avatar', users.cur.avatar);
                usersBox.find('.not-joined button').data('message', users.cur.message_id);
            }
            usersBox.find('.not-joined').show();
        }
    }
}
/*index end  ----------------*/

/*---------instant prize------------*/
function onInstantDraw(){
    if (!getCookie('token')) {
        $('#loginInModal').modal('show');
        return false;
    }
    let box = document.querySelector('.animate-box');
    box.classList.remove('animated', 'heartBeat');
    // Swal.showLoading();
    ajaxRequest('POST', window.location.pathname, {}, (res) => {
        Swal.close();
        instantWinCallback(res);
        }, (err)=>{
        console.log(err);
        Swal.close();
        instantLoseCallback(err);
    });
}

function instantWinCallback(res) {
    // TODO 设置读取是否六小时内参与过的cookie，做前端验证
    if (res.claim) {
        $('#showBox').attr('src', '/static/img/win.gif');
        setTimeout(function () {
            Swal.fire({
                title: "Congrats!",
                text: res.msg,
                type: 'success',
                backdrop:` rgba(2,2,14,0.44) url("/static/img/nyan-cat.gif") center left no-repeat`,
                confirmButtonColor: '#ff8f17',
                confirmButtonText: 'Claim your prize'
            }).then((result) => {
                if (result.value) {
                    window.location.href = 'https://m.me/surprizeday';
                }
            });
        }, 1500);
    } else {
        $('#showBox').attr('src', '/static/img/lose.gif');
        setTimeout(function () {
            Swal.fire(
                'Unfortunately',
                res.msg,
                'info'
            );
        }, 1500);
    }
}

function instantLoseCallback(res) {
    console.log(res);
    let msg = res.msg ? res.msg : 'Error occurred, please try later';
    Swal.fire(
        'Error',
        msg,
        'error'
    );
}

/*---------instant prize------------*/


$('.slideButton').on('click', function(){
    $('.toggle-menu').slideToggle('fast');
});

if (token){
    setTokenHeader(token);
}
