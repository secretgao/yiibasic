;(function (window, undefined) {
  var treeView = tools.$('#treeView');
  var fileData = json;

  // 初始化
  treeView.innerHTML = treeHtml(fileData, 0);

  // 事件
  var fileItem = tools.$('.treeNode');
  var root_icon = tools.$('.icon-control', fileItem[0])[0];

  //root_icon.className = 'icon icon-control icon-minus';
  tools.$('.icon-file', fileItem[0])[0].className = 'icon icon-file icon-file-open';

  tools.each(fileItem, function (item) {
    filesHandle(item);
  });

  $('.treeNode-div').each(function(){
    var remark = $(this).parent().attr('data-remark'),
        uploadTime = $(this).parent().attr('data-uploadTime');
    if(remark == 'undefined'){
      remark = ''; 
    };
    console.log(remark)
    if(uploadTime == 'undefined'){
      uploadTime = ''; 
    };
    if(remark != '' || uploadTime != ''){
        $(this).poshytip({
          content: remark + '上传时间：' + uploadTime,
          className: 'tip-twitter',
          showTimeout: 1,
          alignTo: 'target',
          alignX: 'center',
          offsetX: 0,
          offsetY: 0,
          allowTipHover: false,
          fade: false,
          slide: false
        });
    };
  });
  

  function treeHtml(fileData, fileId) {
    var _html = '';
    var children = getChildById(fileData, fileId);
    var hideChild = fileId > 0 ? 'none' : '';

    _html += '<ul class="'+hideChild+'">';

    children.forEach(function (item, index) {
      var level = getLevelById(fileData, parseInt(item.id));
      var distance = (level - 1) * 20 + 'px';
      var hasChild = hasChilds(fileData, parseInt(item.id));
      var className = hasChild ? '' : 'treeNode-empty';
      var treeRoot_cls = fileId === 0 ? 'treeNode-cur' : '';
      var icon = 'icon icon-file icon-file-close';
      var type = item.type;
      if(type == 1){
        icon = 'icon icon-img';
      }else if(type == 5){
        icon = 'icon icon-music';
      }else if(type == 2){
        icon = 'icon icon-video';
      }else if(type != 0){
        icon = 'icon icon-txt';
      };

      var href = '',
          hrefEnd = '';
      if(type != 0){
        href = '<a href="'+ item.url +'" target="_blank">';
        hrefEnd = '</a>';
      };

      _html += `
        <li>
          ${href}
          <div class="treeNode ${className} ${treeRoot_cls}" style="padding-left: ${distance}" data-file-id="${item.id}" data-remark="${item.remark}" data-uploadTime="${item.uploadTime}">
            <div class="treeNode-div">
            <i class="icon icon-control icon-add"></i>
            <i class="${icon}"></i>
            <span class="title">${!!item.name ? item.name : '　'}</span>
            </div>
          </div>
          ${hrefEnd}
          ${treeHtml(fileData, item.id)}
        </li>`;
    });

    _html += '</ul>';

    return _html;
  };

  function filesHandle(item) {
    tools.addEvent(item, 'click', function () {
      var treeNode_cur = tools.$('.treeNode-cur')[0];
      var fileId = item.dataset.fileId;
      var curElem = document.querySelector('.treeNode[data-file-id="'+fileId+'"]');
      var hasChild = hasChilds(fileData, fileId);
      var icon_control = tools.$('.icon-control', item)[0];
      var icon_file = tools.$('.icon-file', item)[0];
      var openStatus = true;
      tools.removeClass(treeNode_cur, 'treeNode-cur');
      tools.addClass(curElem, 'treeNode-cur');

      if (hasChild) {
        openStatus = tools.toggleClass(item.nextElementSibling, 'none');

        if (openStatus) {
          icon_control.className = 'icon icon-control icon-add';
          icon_file.className = 'icon icon-file icon-file-close';
        } else {
          icon_control.className = 'icon icon-control icon-minus';
          icon_file.className = 'icon icon-file icon-file-open';
        }
      }
    });
  };
})(window);
$(function(){
  function ulshow(_this){
      var ul = _this.closest('ul');
      if(ul.hasClass('none') && ul.prev().length != 0){
        ul.prev().trigger('click');
      };
      if(ul.prev().closest('ul').length != 0){
        ulshow(ul.prev());
      };
    };
  $('.search-btn').on('click', function(){
    $('.treeNode').removeClass('treeNode-search-cur');
    var value = $.trim($('.search-text').val());
    if(value == ''){
      return;
    };
    $('.title').each(function(){
      var title = $(this).text();
      if(title.indexOf(value) != -1){
        $(this).closest('.treeNode').addClass('treeNode-search-cur');
        if($(this).closest('ul').length != 0){
          ulshow($(this));
        };
        
      };
    });
  });
  $('.search-text').on('focus', function(){
      $(this).off('keydown').on('keydown', function(e){
        if(e.which == 13){
          $('.search-btn').trigger('click');
        };
      });
  });


  
});