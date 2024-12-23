<?php

class qa_poll_event
{
    function process_event($event, $userid, $handle, $cookieid, $params)
    {
        if (!qa_opt('poll_enable')) {
            return;
        }
        if (!in_array($event, ['q_post', 'q_queue'])) {
            return;
        }
        if (qa_post_text('is_poll') === null) {
            return;
        }
        qa_db_query_sub(
            'CREATE TABLE IF NOT EXISTS ^postmeta (
								meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
								post_id bigint(20) unsigned NOT NULL,
								meta_key varchar(255) DEFAULT \'\',
								meta_value longtext,
								PRIMARY KEY (meta_id),
								KEY post_id (post_id),
								KEY meta_key (meta_key)
								) ENGINE=MyISAM  DEFAULT CHARSET=utf8'
        );
        qa_db_query_sub(
            'CREATE TABLE IF NOT EXISTS ^polls (
								id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
								parentid bigint(20) unsigned NOT NULL,
								votes longtext,
								content varchar(255) DEFAULT \'\',
								PRIMARY KEY (id)
								) ENGINE=MyISAM  DEFAULT CHARSET=utf8'
        );
        qa_db_query_sub(
            'INSERT INTO ^postmeta (post_id,meta_key,meta_value) VALUES (#,$,$)',
            $params['postid'], 'is_poll', (qa_post_text('poll_multiple') ? '2' : '1')
        );

        $c = 0;
        while (isset($_POST['poll_answer_' . (++$c)])) {
            if (!qa_post_text('poll_answer_' . $c)) {
                continue;
            } // empty
            qa_db_query_sub(
                'INSERT INTO ^polls (parentid,content) VALUES (#,$)',
                $params['postid'], qa_post_text('poll_answer_' . $c)
            );
        }
    }
}
