<?php

require_once(INCLUDE_DIR . 'class.staff.php');

class StaffAjaxAPI extends AjaxController {

    // CHANGED!
    function alertTimeTrackingShift() {
        global $ost, $thisstaff;
        
        if (!$thisstaff)
            Http::response(403, 'Agent login required');
        
        $time_tracking_running = $thisstaff->isTimeTrackingRunning();
        $should_start_stop_time_tracking = $thisstaff->shouldStartOrStopTimeTracking();

        $response = array();
        if (!!$time_tracking_running && $should_start_stop_time_tracking) {
            $response['action'] = 'stop';
            $response['link_redirect'] = $time_tracking_running[1] ? 'tasks.php?id='.$time_tracking_running[1] : 'tickets.php?id='.$time_tracking_running[0];
        } elseif (!$time_tracking_running && $should_start_stop_time_tracking) {
            $response['action'] = 'start';
        }
        return $this->encode($response);
    }
    
    function getTimeTrackingStaffs() {
        global $ost, $thisstaff;
        
        if (!$thisstaff)
            Http::response(403, 'Agent login required');

        // global $thisstaff;
        /*$sql = "SELECT CONCAT(ost_staff.firstname, ' ', ost_staff.lastname) name,
                ost_ticket_time_tracking.ticket_id, ost_ticket__cdata.subject, ost_ticket_time_tracking.task_id, ost_task__cdata.title, ost_ticket_time_tracking.start_time
                FROM ost_ticket_time_tracking
                INNER JOIN ost_staff ON (ost_staff.staff_id = ost_ticket_time_tracking.staff_id)
                INNER JOIN ost_ticket__cdata ON (ost_ticket__cdata.ticket_id = ost_ticket_time_tracking.ticket_id)
                LEFT OUTER JOIN ost_task__cdata ON (ost_task__cdata.task_id = ost_ticket_time_tracking.task_id)
                WHERE end_time IS NULL";*/
	$sql = "SELECT CONCAT(".STAFF_TABLE.".firstname, ' ', ".STAFF_TABLE.".lastname) name,
                ".TICKET_TIME_TRACKING_TABLE.".ticket_id, ".TICKET_CDATA_TABLE.".subject, ".TICKET_TIME_TRACKING_TABLE.".task_id, ".TASK_CDATA_TABLE.".title, ".TICKET_TIME_TRACKING_TABLE.".start_time
                FROM ".TICKET_TIME_TRACKING_TABLE."
                INNER JOIN ".STAFF_TABLE." ON (".STAFF_TABLE.".staff_id = ".TICKET_TIME_TRACKING_TABLE.".staff_id)
                INNER JOIN ".TICKET_CDATA_TABLE." ON (".TICKET_CDATA_TABLE.".ticket_id = ".TICKET_TIME_TRACKING_TABLE.".ticket_id)
                LEFT OUTER JOIN ".TASK_CDATA_TABLE." ON (".TASK_CDATA_TABLE.".task_id = ".TICKET_TIME_TRACKING_TABLE.".task_id)
                WHERE end_time IS NULL";
        $result = db_query($sql);
        $rows = array();
        while ($ht = db_fetch_array($result)) {
            $row = array();
            $row['staff'] = $ht['name'];
            $row['ticket_id'] = $ht['ticket_id'];
            $row['ticket_title'] = $ht['subject'];
            $row['task_id'] = $ht['task_id'];
            $row['task_title'] = $ht['title'];
            $row['start_time'] = $ht['start_time'];
            $rows[] = $row;
        }
        return $this->encode($rows);
    }

    function getTicketTimeTrackingDay($date) {
        global $ost, $thisstaff;
        
        if (!$thisstaff)
            Http::response(403, 'Agent login required');
        if (!$thisstaff->isAdmin())
            Http::response(403, 'Access denied');

        $date = db_input($date);
        /*$sql = '
            SELECT ost_ticket.ticket_id, ost_staff.username user_time_tracking, ost_ticket.number number_ticket, ost_task.id task_id,
	    SUM(TIMESTAMPDIFF(MINUTE, ost_ticket_time_tracking.start_time, ost_ticket_time_tracking.end_time)) minutes
            FROM ost_ticket
            LEFT OUTER JOIN ost_ticket_time_tracking ON (ost_ticket.ticket_id = ost_ticket_time_tracking.ticket_id)
            LEFT OUTER JOIN ost_staff ON (ost_staff.staff_id = ost_ticket_time_tracking.staff_id)
            LEFT OUTER JOIN ost_task ON (ost_task.id = ost_ticket_time_tracking.task_id)
            WHERE (ost_ticket.status_id IN (2, 3) OR ost_task.flags = 0) AND (DATE(ost_task.closed) = DATE('.$date.') OR DATE(ost_ticket.closed) = DATE('.$date.'))
	    GROUP BY ost_ticket.ticket_id, ost_staff.username, ost_ticket.number, ost_task.id
        ';*/
	 $sql = '
            SELECT '.TICKET_TABLE.'.ticket_id, '.STAFF_TABLE.'.username user_time_tracking, '.TICKET_TABLE.'.number number_ticket, '.TASK_TABLE.'.id task_id,
	    SUM(TIMESTAMPDIFF(MINUTE, '.TICKET_TIME_TRACKING_TABLE.'.start_time, '.TICKET_TIME_TRACKING_TABLE.'.end_time)) minutes
            FROM '.TICKET_TABLE.'
            LEFT OUTER JOIN '.TICKET_TIME_TRACKING_TABLE.' ON ('.TICKET_TABLE.'.ticket_id = '.TICKET_TIME_TRACKING_TABLE.'.ticket_id)
            LEFT OUTER JOIN '.STAFF_TABLE.' ON ('.STAFF_TABLE.'.staff_id = '.TICKET_TIME_TRACKING_TABLE.'.staff_id)
            LEFT OUTER JOIN '.TASK_TABLE.' ON ('.TASK_TABLE.'.id = '.TICKET_TIME_TRACKING_TABLE.'.task_id)
            WHERE ('.TICKET_TABLE.'.status_id IN (2, 3) OR '.TASK_TABLE.'.flags = 0) AND (DATE('.TASK_TABLE.'.closed) = DATE('.$date.') OR DATE('.TICKET_TABLE.'.closed) = DATE('.$date.'))
	    GROUP BY '.TICKET_TABLE.'.ticket_id, '.STAFF_TABLE.'.username, '.TICKET_TABLE.'.number, '.TASK_TABLE.'.id
        ';
        $result = db_query($sql);
        $table = '<style>
        table {
	  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
          border-collapse: collapse;
          width: 100%;
        }
        
        table td, table th {
          border: 1px solid #ddd;
          padding: 8px;
        }
        
        table tr:nth-child(even){background-color: #f2f2f2;}
        
        table tr:hover {background-color: #ddd;}
        
        table th {
          padding-top: 12px;
          padding-bottom: 12px;
          text-align: left;
          background-color: #4CAF50;
          color: white;
        }
        </style>';
        $table .= '<table>';
        $table .= '<tr>';
        $table .= '<td>user_time_tracking</td>';
        $table .= '<td>number_ticket</td>';
        $table .= '<td>task_id</td>';
        $table .= '<td>minutes</td>';
        $table .= '</tr>';
        while ($ht = db_fetch_array($result)) {
            $table .= '<tr>';
            $table .= '<td>'.$ht['user_time_tracking'].'</td>';
            $table .= '<td><a target="_blank" href="/scp/tickets.php?id='.$ht['ticket_id'].'">'.$ht['number_ticket'].'</a></td>';
            $table .= '<td><a target="_blank" href="/scp/tasks.php?id='.$ht['task_id'].'">'.$ht['task_id'].'</a></td>';
            $table .= '<td>'.$ht['minutes'].'</td>';
            $table .= '</tr>';
        }
        $table .= '</table>';
        return $table;
    }
    // CHANGED!

  /**
   * Ajax: GET /staff/<id>/set-password
   *
   * Uses a dialog to add a new department
   *
   * Returns:
   * 200 - HTML form for addition
   * 201 - {id: <id>, name: <name>}
   *
   * Throws:
   * 403 - Not logged in
   * 403 - Not an administrator
   * 404 - No such agent exists
   */
  function setPassword($id) {
      global $ost, $thisstaff;

      if (!$thisstaff)
          Http::response(403, 'Agent login required');
      if (!$thisstaff->isAdmin())
          Http::response(403, 'Access denied');
      if ($id && !($staff = Staff::lookup($id)))
          Http::response(404, 'No such agent');

      $form = new PasswordResetForm($_POST);
      $errors = array();
      if (!$_POST && isset($_SESSION['new-agent-passwd']))
          $form->data($_SESSION['new-agent-passwd']);

      if ($_POST && $form->isValid()) {
          $clean = $form->getClean();
          try {
              // Validate password
              if (!$clean['welcome_email'])
                  PasswordPolicy::checkPassword($clean['passwd1'], null);
              if ($id == 0) {
                  // Stash in the session later when creating the user
                  $_SESSION['new-agent-passwd'] = $clean;
                  Http::response(201, 'Carry on');
              }
              if ($clean['welcome_email']) {
                  $staff->sendResetEmail();
              }
              else {
                  $staff->setPassword($clean['passwd1'], null);
                  if ($clean['change_passwd'])
                      $staff->change_passwd = 1;
              }
              if ($staff->save())
                  Http::response(201, 'Successfully updated');
          }
          catch (BadPassword $ex) {
              $passwd1 = $form->getField('passwd1');
              $passwd1->addError($ex->getMessage());
          }
          catch (PasswordUpdateFailed $ex) {
              $errors['err'] = __('Password update failed:').' '.$ex->getMessage();
          }
      }

      $title = __("Set Agent Password");
      $verb = $id == 0 ? __('Set') : __('Update');
      $path = ltrim($ost->get_path_info(), '/');

      include STAFFINC_DIR . 'templates/quick-add.tmpl.php';
  }

    function changePassword($id) {
        global $cfg, $ost, $thisstaff;

        if (!$thisstaff)
            Http::response(403, 'Agent login required');
        if (!$id || $thisstaff->getId() != $id)
            Http::response(404, 'No such agent');

        $form = new PasswordChangeForm($_POST);
        $errors = array();

        if ($_POST && $form->isValid()) {
            $clean = $form->getClean();
            if (($rtoken = $_SESSION['_staff']['reset-token'])) {
                $_config = new Config('pwreset');
                if ($_config->get($rtoken) != $thisstaff->getId())
                    $errors['err'] =
                        __('Invalid reset token. Logout and try again');
                elseif (!($ts = $_config->lastModified($rtoken))
                        && ($cfg->getPwResetWindow() < (time() - strtotime($ts))))
                    $errors['err'] =
                        __('Invalid reset token. Logout and try again');
            }
            if (!$errors) {
                try {
                    $thisstaff->setPassword($clean['passwd1'], @$clean['current']);
                    if ($thisstaff->save()) {
                        if ($rtoken) {
                            $thisstaff->cancelResetTokens();
                            Http::response(200, $this->encode(array(
                                'redirect' => 'index.php'
                            )));
                        }
                        Http::response(201, 'Successfully updated');
                    }
                }
                catch (BadPassword $ex) {
                    $passwd1 = $form->getField('passwd1');
                    $passwd1->addError($ex->getMessage());
                }
                catch (PasswordUpdateFailed $ex) {
                    $errors['err'] = __('Password update failed:').' '.$ex->getMessage();
                }
            }
        }

        $title = __("Change Password");
        $verb = __('Update');
        $path = ltrim($ost->get_path_info(), '/');

        include STAFFINC_DIR . 'templates/quick-add.tmpl.php';
    }

    function getAgentPerms($id) {
        global $thisstaff;

        if (!$thisstaff)
            Http::response(403, 'Agent login required');
        if (!$thisstaff->isAdmin())
            Http::response(403, 'Access denied');
        if (!($staff = Staff::lookup($id)))
            Http::response(404, 'No such agent');

        return $this->encode($staff->getPermissionInfo());
    }

    function resetPermissions() {
        global $ost, $thisstaff;

        if (!$thisstaff)
            Http::response(403, 'Agent login required');
        if (!$thisstaff->isAdmin())
            Http::response(403, 'Access denied');

        $form = new ResetAgentPermissionsForm($_POST);

        if (@is_array($_GET['ids'])) {
            $perms = new RolePermission(null);
            $selected = Staff::objects()->filter(array('staff_id__in' => $_GET['ids']));
            foreach ($selected as $staff)
                // XXX: This maybe should be intersection rather than union
                $perms->merge($staff->getPermission());
            $form->getField('perms')->setValue($perms->getInfo());
        }

        if ($_POST && $form->isValid()) {
            $clean = $form->getClean();
            Http::response(201, $this->encode(array('perms' => $clean['perms'])));
        }

        $title = __("Reset Agent Permissions");
        $verb = __("Continue");
        $path = ltrim($ost->get_path_info(), '/');

        include STAFFINC_DIR . 'templates/reset-agent-permissions.tmpl.php';
    }

    function changeDepartment() {
        global $ost, $thisstaff;

        if (!$thisstaff)
            Http::response(403, 'Agent login required');
        if (!$thisstaff->isAdmin())
            Http::response(403, 'Access denied');

        $form = new ChangeDepartmentForm($_POST);

        // Preselect reasonable dept and role based on the current  settings
        // of the received staff ids
        if (@is_array($_GET['ids'])) {
            $dept_id = null;
            $role_id = null;
            $selected = Staff::objects()->filter(array('staff_id__in' => $_GET['ids']));
            foreach ($selected as $staff) {
                if (!isset($dept_id)) {
                    $dept_id = $staff->dept_id;
                    $role_id = $staff->role_id;
                }
                elseif ($dept_id != $staff->dept_id)
                    $dept_id = 0;
                elseif ($role_id != $staff->role_id)
                    $role_id = 0;
            }
            $form->getField('dept_id')->setValue($dept_id);
            $form->getField('role_id')->setValue($role_id);
        }

        if ($_POST && $form->isValid()) {
            $clean = $form->getClean();
            Http::response(201, $this->encode($clean));
        }

        $title = __("Change Primary Department");
        $verb = __("Continue");
        $path = ltrim($ost->get_path_info(), '/');

        include STAFFINC_DIR . 'templates/quick-add.tmpl.php';
    }

    function setAvatar($id) {
        global $thisstaff;

        if (!$thisstaff)
            Http::response(403, 'Agent login required');
        if ($id != $thisstaff->getId() && !$thisstaff->isAdmin())
            Http::response(403, 'Access denied');
        if ($id == $thisstaff->getId())
            $staff = $thisstaff;
        else
            $staff = Staff::lookup((int) $id);

        if (!($avatar = $staff->getAvatar()))
            Http::response(404, 'User does not have an avatar');

        if ($code = $avatar->toggle())
          return $this->encode(array(
            'img' => (string) $avatar,
            // XXX: This is very inflexible
            'code' => $code,
          ));
    }
}
