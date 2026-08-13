<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| PAGINATION
| -------------------------------------------------------------------
| Shared configuration for the Pagination library. Templates are styled
| with Tailwind CSS to match the rest of the application. Controllers
| may override individual keys via $this->pagination->initialize().
|
| Page numbers use query strings (page=2) so search/filter parameters
| can be preserved by enabling 'reuse_query_string'.
|
*/

$config['use_page_numbers'] = TRUE;
$config['num_links'] = 2;

// Query-string pagination (products?page=2&q=...&category=...)
$config['page_query_string'] = TRUE;
$config['query_string_segment'] = 'page';
$config['reuse_query_string'] = TRUE;

// Wrapper
$config['full_tag_open'] = '<nav class="mt-6 flex items-center justify-center" aria-label="Pagination">';
$config['full_tag_close'] = '</nav>';

// Prev / Next
$config['prev_link'] = 'Previous';
$config['next_link'] = 'Next';
$config['prev_tag_open'] = '<span class="mr-1">';
$config['prev_tag_close'] = '</span>';
$config['next_tag_open'] = '<span class="ml-1">';
$config['next_tag_close'] = '</span>';

// First / Last
$config['first_link'] = 'First';
$config['last_link'] = 'Last';
$config['first_tag_open'] = '<span class="mr-1">';
$config['first_tag_close'] = '</span>';
$config['last_tag_open'] = '<span class="ml-1">';
$config['last_tag_close'] = '</span>';

// Numbered page links and the current page
$config['num_tag_open'] = '<span class="mx-0.5">';
$config['num_tag_close'] = '</span>';
$config['cur_tag_open'] = '<span class="mx-0.5 inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-indigo-600 px-3 text-sm font-semibold text-white">';
$config['cur_tag_close'] = '</span>';

// Style shared by every generated link
$config['attributes'] = array(
	'class' => 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600',
);
