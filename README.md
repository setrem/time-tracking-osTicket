# Time Tracking osTicket

Simple time tracking for osTicket. The customizations were tested on osTicket version v1.14.2.


# How to use

## Execute the SQLs

In table `ost_ticket_time_tracking` will be saved the hours spend on each ticket/task.

```sql
CREATE TABLE ost_ticket_time_tracking (
	ticket_id INT(11) UNSIGNED NOT NULL,
	task_id INT(11) UNSIGNED NULL,
	staff_id INT(10) UNSIGNED NOT NULL,
	start_time DATETIME NOT NULL,
	end_time DATETIME NULL,
	PRIMARY KEY (ticket_id, staff_id, start_time),
	CONSTRAINT `ticket_id_time_tracking` FOREIGN KEY (ticket_id) REFERENCES ost_ticket (ticket_id),
	CONSTRAINT `task_id_time_tracking` FOREIGN KEY (task_id) REFERENCES ost_task (id),
	CONSTRAINT `staff_id_time_tracking` FOREIGN KEY (staff_id) REFERENCES ost_staff (staff_id)
);
```

In table `ost_staff_shifts` will be saved the shifts of each staff.

```sql
CREATE TABLE ost_staff_shifts (
	staff_id INT(10) UNSIGNED NOT NULL,
	shift1_start TIME NOT NULL,
	shift1_end TIME NOT NULL,
	shift2_start TIME NOT NULL,
	shift2_end TIME NOT NULL,
	PRIMARY KEY (staff_id),
	CONSTRAINT `staff_id_shitfs` FOREIGN KEY (staff_id) REFERENCES ost_staff (staff_id)
);
```


## Change osTicket files

All changes made to the code are in the [osTicket](/osTicket) folder. The folder follows the same structure as the osTicket version v1.14.2 released [here](https://github.com/osTicket/osTicket/releases/tag/v1.14.2).

All changes made were placed between the `// CHANGED!` mark in PHP and JS files, and between `<!-- CHANGED! -->` in HTML.


# To do
- Report of hours spend on each ticket/task.
- Report of hours tracked by each `staff_id`.
- Change the table `ost_staff_shifts` to support different shifts on each day of the week.
- Create script to install the plugin.
- Use translations.
