<?php

namespace Altum\Controllers;

use Altum\Database\Database;

class AdminStatistics extends Controller {
    public $type;
    public $datetime;

    public function index() {

        $this->type = (isset($this->params[0])) && in_array($this->params[0], ['payments', 'growth', 'links']) ? Database::clean_string($this->params[0]) : 'growth';

        $this->datetime = \Altum\Date::get_start_end_dates_new();

        /* Process only data that is needed for that specific page */
        $type_data = $this->{$this->type}();

        /* Main View */
        $data = [
            'type' => $this->type,
            'datetime' => $this->datetime
        ];
        $data = array_merge($data, $type_data);

        $view = new \Altum\Views\View('admin/statistics/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    protected function payments() {

        $payments_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total_payments`, DATE_FORMAT(`date`, '{$this->datetime['query_date_format']}') AS `formatted_date`, TRUNCATE(SUM(`total_amount`), 2) AS `total_amount` FROM `payments` WHERE `date` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $payments_chart[$row->formatted_date] = [
                'total_amount' => $row->total_amount,
                'total_payments' => $row->total_payments
            ];

        }

        $payments_chart = get_chart_data($payments_chart);

        return [
            'payments_chart' => $payments_chart
        ];

    }

    protected function growth() {

        /* Users */
        $users_chart = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 DATE_FORMAT(`date`, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                 `users`
            WHERE
                `date` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $users_chart[$row->formatted_date] = [
                'users' => $row->total
            ];
        }

        $users_chart = get_chart_data($users_chart);

        /* Projects */
        $projects_chart = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                 `projects`
            WHERE
                `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $projects_chart[$row->formatted_date] = [
                'projects' => $row->total
            ];
        }

        $projects_chart = get_chart_data($projects_chart);

        /* Links */
        $links_chart = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 DATE_FORMAT(`date`, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                 `links`
            WHERE
                `date` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $links_chart[$row->formatted_date] = [
                'links' => $row->total
            ];
        }

        $links_chart = get_chart_data($links_chart);

        /* Users logs */
        $users_logs_chart = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 DATE_FORMAT(`date`, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                 `users_logs`
            WHERE
                `date` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $users_logs_chart[$row->formatted_date] = [
                'users_logs' => $row->total
            ];
        }

        $users_logs_chart = get_chart_data($users_logs_chart);

        /* Redeemed codes */
        if(in_array(settings()->license->type, ['SPECIAL', 'Extended License'])) {
            $redeemed_codes_chart = [];
            $result = database()->query("
                SELECT
                     COUNT(*) AS `total`,
                     DATE_FORMAT(`date`, '{$this->datetime['query_date_format']}') AS `formatted_date`
                FROM
                     `redeemed_codes`
                WHERE
                    `date` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}'
                GROUP BY
                    `formatted_date`
                ORDER BY
                    `formatted_date`
            ");
            while ($row = $result->fetch_object()) {

                if($this->date->start_date == $this->date->end_date) {
                    $formatted_date = explode(' ', $row->formatted_date);
                    $row->formatted_date = ((new \DateTime($formatted_date[0]))->setTime($formatted_date[1], 0)->setTimezone(new \DateTimeZone(\Altum\Date::$timezone))->format('H A'));
                } else {
                    $row->formatted_date = \Altum\Date::get($row->formatted_date, 2);
                }

                $redeemed_codes_chart[$row->formatted_date] = [
                    'redeemed_codes' => $row->total
                ];
            }

            $redeemed_codes_chart = get_chart_data($redeemed_codes_chart);
        }

        return [
            'users_chart' => $users_chart,
            'projects_chart' => $projects_chart,
            'links_chart' => $links_chart,
            'users_logs_chart' => $users_logs_chart,
            'redeemed_codes_chart' => $redeemed_codes_chart ?? null
        ];
    }
    protected function links() {

        $track_links_chart = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                 `track_links`
            WHERE
                `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $track_links_chart[$row->formatted_date] = [
                'track_links' => $row->total
            ];
        }

        $track_links_chart = get_chart_data($track_links_chart);


        return [
            'track_links_chart'   => $track_links_chart
        ];
    }

}
