(function (window) {
	'use strict';

	// Your starting point. Enjoy the ride!

    function toggleAllControl() {
        checkAll[0].checked = (!tasksContainer.find('li:not(.completed)').length && tasksContainer.find('li.completed').length);

        if (!tasksContainer.find('li').length) {
          sectionMain.addClass('hidden');
        } else {
          sectionMain.removeClass('hidden');
        }
    }

    var infoDiv = $('div.nav-bar.info-message');
    var listsContainer = $('div.nav-bar-lists');
    var tasksContainer = $('ul.todo-list');
    var tasksCount = $('span.todo-count strong');
    var removeCompleted = $('button.remove-completed');
    var sectionMain = $('section.main');
    var checkAll = $('input#toggle-all');

    // AUTH STUFF
    var form = $('form[name="log_reg_form"]');
    var form_list_edit = $('form[name="edit_list_form"]');

    $(document).ready(function () {
        if (!tasksContainer.find('li:not(.completed)').length && tasksContainer.find('li.completed').length) {
            checkAll[0].checked = true;
        }

        if (tasksContainer.find('li').length) {
          sectionMain.removeClass('hidden');
        }

        var filter = localStorage.getItem('filter');
        switch (filter) {
          case 'all':
            $('ul.filters li a[href="#/"]').trigger('click');
            break;
          case 'active':
            $('ul.filters li a[href="#/active"]').trigger('click');
            break;
          case 'completed':
            $('ul.filters li a[href="#/completed"]').trigger('click');
            break;
          default:
            $('ul.filters li a[href="#/"]').trigger('click');
            break;
        }

        var lists = localStorage.getItem('lists');
        switch (lists) {
            case 'open':
                $('form[name="log_reg_form"] button.show-all-lists').trigger('click');
                break;
        }

        $(document).on('click', function () {
            if (!infoDiv.hasClass('hidden')) {
                infoDiv.removeClass('error-message').removeClass('success-message').html('').addClass('hidden');
            }
        });
    });



    $(document).on('click', 'form[name="log_reg_form"] button.show-signup', function (e) {
        $.ajax({
            url: '/auth/showSignUp',
            success: function (response) {
                form.html(response);
            }
        });
    });

    $(document).on('click', 'form[name="log_reg_form"] button.show-login', function (e) {
        $.ajax({
            url: '/auth/showLogin',
            success: function (response) {
                form.html(response);
            }
        });
    });

    $(document).on('click', 'form[name="log_reg_form"] button.back', function (e) {
        $.ajax({
            url: '/auth/goBack',
            success: function (response) {
                form.html(response);
            }
        });
    });

    $(document).on('click', 'form[name="log_reg_form"] button.signup', function (e) {
        var formData = new FormData(document.forms.log_reg_form);
        $.ajax({
            url: '/auth/reg',
            method: 'POST',
            data: {
                email: formData.get('email'),
                passwd: formData.get('passwd'),
                passwd_conf: formData.get('passwd_conf')
            },
            beforeSend: function(xhr) {
                viewMask('wait...');
            },
            complete:function(){
                hideMask();
            },
            success: function (response) {
                if (response.error) {
                    infoDiv.addClass('error-message');
                } else {
                    form[0].reset();
                    infoDiv.addClass('success-message');
                }
                infoDiv.html(response.msg).removeClass('hidden');
            }
        });
    });

    $(document).on('click', 'form[name="log_reg_form"] button.login', function (e) {
        var formData = new FormData(document.forms.log_reg_form);
        $.ajax({
            url: '/auth/login',
            method: 'POST',
            data: {
                email: formData.get('email'),
                passwd: formData.get('passwd')
            },
            success: function (response) {
                var error = response.error;
                if (error) {
                    $.ajax({
                        url: '/engine/showMessage',
                        data: {
                            view_name: 'message',
                            msg: response.msg
                        },
                        success: function (response) {
                            infoDiv.addClass('error-message').html(response).removeClass('hidden');
                        }
                    });
                } else {
                    $.ajax({
                        url: '/auth/showAuthorized',
                        data: {
                            email: response.email
                        },
                        success: function (response) {
                            form.html(response);
                          $.ajax({
                            url: '/engine/changeList',
                            success: function (response) {
                              if (!response.error) {
                                form_list_edit.html(response.current_list);
                                form_list_edit.parent().removeClass('hidden');
                                tasksContainer.html(response.tasks);
                                tasksCount.text(response.active_tasks_count);
                                if (response.passive_tasks_count) {
                                  removeCompleted.removeClass('hidden');
                                } else {
                                  removeCompleted.addClass('hidden');
                                }
                                toggleAllControl();
                                $('ul.filters li a.selected').trigger('click');
                              }
                            }
                          });
                        }
                    });
                }
            }
        });
    });

    $(document).on('click', 'form[name="log_reg_form"] button.exit', function (e) {
        $.ajax({
            url: '/auth/logout',
            success: function () {
                $.ajax({
                    url: '/auth/goBack',
                    success: function (response) {
                        form.html(response);
                        listsContainer.addClass('hidden').removeAttr('style');
                        form_list_edit.html('');
                        form_list_edit.parent().addClass('hidden');
                        tasksContainer.html('');
                        tasksCount.text('0');
                        removeCompleted.addClass('hidden');
                        toggleAllControl();
                    }
                });
            }
        });
    });

    $(document).on('keyup', 'input[name="email"], input[name="passwd"], input[name="passwd_conf"]', function (e) {
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if (keycode === 13) {
            $('form[name="log_reg_form"] button.login').trigger('click');
            $('form[name="log_reg_form"] button.signup').trigger('click');
        }
    });
    // END AUTH STUFF

    // LIST WORK
    $(document).on('keyup', 'input[name="list_name"]', function (e) {
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if (keycode === 13) {
            $('form[name="edit_list_form"] button.list-save').trigger('click');
        }
    });

    $('input.new-todo').keyup(function (event) {
        var newTodo = $(this);
        var currentList = $('span.list');
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode === 13) {
            var value = newTodo.val().trim();
            if (value.length) {
                var data = {
                    list_id: 0,
                    task: value
                };

                if (currentList.length) {
                    data.list_id = parseInt(currentList.attr('id').split('-')[1]);
                }

                $.ajax({
                    url: '/lists/addTask',
                    method: 'POST',
                    data: data,
                    success: function (response) {
                        switch (response.error) {
                            case 0:
                                if (response.list_id !== undefined) {
                                    form_list_edit.html(response.list_view);
                                    form_list_edit.parent().removeClass('hidden');
                                }
                                tasksContainer.append(response.task_view);
                                tasksCount.text(parseInt(tasksCount.text())+1);
                                if (!listsContainer.hasClass('hidden')) {
                                    listsContainer.prepend(response.all_lists_add);
                                }
                                newTodo.val('');
                                toggleAllControl();
                                break;
                            case 1:
                            case 2:
                                infoDiv.addClass('error-message').html(response.msg).removeClass('hidden');
                                break;
                        }
                    }
                });
            }
        }
    });

    $(document).on('click','button.destroy', function () {
        var parent_li = $(this).parent().parent();
        var completed = parent_li.hasClass('completed');
        $.ajax({
            url: '/lists/removeTask',
            method: 'POST',
            data: {
                task_id: parent_li.attr('id')
            },
            success: function (response) {
                if (!response.error) {
                    parent_li.remove();
                    if (!completed) {
                        tasksCount.text(parseInt(tasksCount.text())-1);
                    }
                    toggleAllControl();
                }
            }
        });
    });

    $(document).on('click', 'button.remove-completed', function () {
        var tasks = tasksContainer.find('li.completed');
        var clearButton = $(this);
        var removeTasksArr =[];
        tasks.each(function (key, value) {
            removeTasksArr.push(parseInt(value.id));
        });

        $.ajax({
            url: '/lists/removeTasks',
            method: 'POST',
            data: {
                task_ids: JSON.stringify(removeTasksArr)
            },
            success: function (response) {
                if (!response.error) {
                    tasks.remove();
                    clearButton.addClass('hidden');
                    toggleAllControl();
                }
            }
        });
    });

    $(document).on('click','button.edit-list-name', function () {
        var list = $('span.list');
        var list_id = list.attr('id');
        var list_name = list.text();
        $.ajax({
            url: '/lists/showEditList',
            data: {
                list_id: list_id,
                list_name: list_name
            },
            success: function (response) {
                form_list_edit.html(response);
            }
        });
    });

    $(document).on('click','button.list-close', function () {
        var list = $('span.list');
        var list_id = list.attr('id');
        var list_name = list.text();
        if (list_name.trim() === 'default') {
            $.ajax({
                url: '/engine/showMessage',
                data: {
                    view_name: 'message',
                    msg: 'Can not close list with "default" name! Please, edit list name!'
                },
                success: function (response) {
                    infoDiv.addClass('error-message').html(response).removeClass('hidden');
                }
            });
        } else {
            $.ajax({
                url: '/lists/changeActiveList',
                method: 'POST',
                data: {
                    list_id: 0
                },
                success: function (response) {
                    if (!response.error) {
                        form_list_edit.parent().addClass('hidden');
                        form_list_edit.html('');
                        tasksContainer.html('');
                        listsContainer.find('div.selected').removeClass('selected');
                        tasksCount.text('0');
                        removeCompleted.addClass('hidden');
                        toggleAllControl();
                    }
                }
            });
        }
    });

    $(document).on('click','button.list-save', function () {
        var formData = new FormData(document.forms.edit_list_form);
        var prev_list_name = formData.get('prev_list_name').trim();
        var list_name = formData.get('list_name').trim();

        if (prev_list_name === list_name) {
            $.ajax({
                url: '/engine/showMessage',
                data: {
                    view_name: 'message',
                    msg: "Can't save list with the same name!"
                },
                success: function (response) {
                    infoDiv.addClass('error-message').html(response).removeClass('hidden');
                }
            });
        } else {
            var list_id = $('input[name="list_name"]').attr('id');
            $.ajax({
                url: '/lists/updateListName',
                method: 'POST',
                data: {
                    list_id: list_id.split('-')[1],
                    list_name: list_name
                },
                success: function (response) {
                    if (!response.error) {
                        form_list_edit.html(response.msg);
                        listsContainer.find('div.selected').text(list_name);
                    } else {
                        infoDiv.addClass('error-message').html(response.msg).removeClass('hidden');
                    }
                }
            });
        }
    });

    $(document).on('click', 'form[name="edit_list_form"] button.back', function (e) {
        var list = $('input.list-name');
        var hidden_list = $('input.prev-list-name');
        var list_id = list.attr('id').split('-')[1];
        var list_name = hidden_list.val();
        $.ajax({
            url: '/lists/goBack',
            data: {
                list_id: list_id,
                list_name: list_name
            },
            success: function (response) {
                form_list_edit.html(response);
            }
        });
    });

    $(document).on('click', 'form[name="log_reg_form"] button.show-all-lists', function (e) {
        var button = $(this);
        $.ajax({
            url: '/lists/getAllLists',
            success: function (response) {
                if (response.error) {
                    infoDiv.addClass('error-message').html(response.msg).removeClass('hidden');
                } else {
                    button.addClass('hide-all-lists').removeClass('show-all-lists').text('Hide lists');
                    listsContainer.html(response.msg).removeClass('hidden').css({display: 'flex'});
                    localStorage.setItem('lists', 'open');
                }
            }
        });
    });

    $(document).on('click', 'form[name="log_reg_form"] button.hide-all-lists', function (e) {
        $(this).addClass('show-all-lists').removeClass('hide-all-lists').text('Show lists');
        listsContainer.html('').addClass('hidden').removeAttr('style');
        localStorage.setItem('lists', 'close');
    });

    $(document).on('click', 'div.nav-bar-lists div', function () {
        var list = $(this);
        var selectedList = listsContainer.find('div.selected');
        if (selectedList.text() === 'default') {
            $.ajax({
                url: '/engine/showMessage',
                data: {
                    view_name: 'message',
                    msg: 'Can not close list with "default" name! Please, edit current list name!'
                },
                success: function (response) {
                    infoDiv.addClass('error-message').html(response).removeClass('hidden');
                }
            });
        } else {
            if (!list.hasClass('selected')) {
                list.addClass('selected');
                selectedList.removeClass('selected');
                $.ajax({
                    url: '/engine/changeList',
                    method: 'POST',
                    data: {
                        list_id: list.attr('id').split('-')[1]
                    },
                    success: function (response) {
                        if (!response.error) {
                            form_list_edit.html(response.current_list);
                            form_list_edit.parent().removeClass('hidden');
                            tasksContainer.html(response.tasks);
                            tasksCount.text(response.active_tasks_count);
                            if (response.passive_tasks_count) {
                                removeCompleted.removeClass('hidden');
                            } else {
                                removeCompleted.addClass('hidden');
                            }
                            toggleAllControl();
                            $('ul.filters li a.selected').trigger('click');
                        }
                    }
                });
            }
        }

    });
    // END LIST WORK

    // TASK WORK
    $(document).on('click', 'input.toggle', function () {
        var input = $(this);
        var parent_li = input.parent().parent();
        var status = 0;
        if (input[0].checked) {
            status = 1;
        }

        $.ajax({
            url: '/lists/changeTaskStatus',
            method: 'POST',
            data: {
                task_id: parent_li.attr('id'),
                status: status
            },
            success: function (response) {
                if (!response.error) {
                    if (parent_li.hasClass('completed')) {
                        parent_li.removeClass('completed');
                        tasksCount.text(parseInt(tasksCount.text())+1);
                        if (!tasksContainer.find('li.completed').length) {
                            removeCompleted.addClass('hidden');
                        }
                    } else {
                        parent_li.addClass('completed');
                        tasksCount.text(parseInt(tasksCount.text())-1);
                    }
                    toggleAllControl();

                    $('ul.filters li a.selected').trigger('click');
                }
            }
        });
    });

    $('ul.filters li a').click(function () {
        var link = $(this);
        var href = link.attr('href');
        var selected = link.hasClass('selected');

        $('ul.filters').find('li a.selected').removeClass('selected');
        link.addClass('selected');
        switch (href) {
            case '#/':
                tasksContainer.find('li.hidden').removeClass('hidden');
                localStorage.setItem('filter', 'all');
                break;
            case '#/active':
                tasksContainer.find('li.completed').addClass('hidden');
                tasksContainer.find('li:not(.completed)').removeClass('hidden');
                localStorage.setItem('filter', 'active');
                break;
            case '#/completed':
                tasksContainer.find('li.completed').removeClass('hidden');
                tasksContainer.find('li:not(.completed)').addClass('hidden');
                localStorage.setItem('filter', 'completed');
                break;
        }
    });

    $('input#toggle-all').click(function () {
        var toggleAll = $(this)[0];
        var currentList = $('span.list');
        var status = 0;
        if (toggleAll.checked) {
            status = 1;
        }
        $.ajax({
            url: '/lists/changeAllTasksStatus',
            method: 'POST',
            data: {
                status: status,
                list_id: parseInt(currentList.attr('id').split('-')[1])
            },
            success: function (response) {
                if (!response.error) {
                    if (toggleAll.checked) {
                        tasksContainer.find('input.toggle').each(function (key, value) {
                            value.checked = true;
                        });
                        tasksContainer.find('li').addClass('completed');
                        tasksCount.text('0');
                        removeCompleted.removeClass('hidden');
                    } else {
                        tasksContainer.find('input.toggle').each(function (key, value) {
                            value.checked = false;
                        });
                        tasksContainer.find('li').removeClass('completed');
                        tasksCount.text(tasksContainer.find('li').length);
                        removeCompleted.addClass('hidden');
                    }
                    $('ul.filters li a.selected').trigger('click');
                }
            }
        });

    });
    // TASK WORK END

  // $(function(){
  //   $.ajaxSetup({
  //     beforeSend: function(xhr) {
  //       viewMask('wait...');
  //     },
  //     complete:function(){
  //       hideMask();
  //     }
  //   });
  // });

  var hideMask = function(){
    $('#mask').fadeOut(300);
  };

  var viewMask = function(str) {
    var mask = '<div id="mask"></div>';
    var txt = $('<div id="maskTxt">'+str+'</div>').css({
      'text-align':'center',
      'padding-top':'20%',
      'color':'white',
      'font-size':'25px'
    });
    if($('#mask').length <= 0) {
      $('body').append(mask);
    }
    $('#mask').css({
      'position':'fixed',
      'width':'100%',
      'height':'100%',
      'background':' rgba(0,0,0,.7)',
      'top':'0',
      'left':'0',
      'display':'none',
      'z-index':'100'
    });

    if($('#maskTxt').length <= 0) { $('#mask').append(txt); }
    $('#mask').fadeIn(300);
  };
})(window);