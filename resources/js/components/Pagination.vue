<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  paginator: { type: Object, required: true },
});

const meta = computed(() => ({
  current: props.paginator.current_page ?? props.paginator.currentPage ?? 1,
  last: props.paginator.last_page ?? props.paginator.lastPage ?? 1,
  from: props.paginator.from ?? props.paginator.firstItem ?? 0,
  to: props.paginator.to ?? props.paginator.lastItem ?? (props.paginator.data ? props.paginator.data.length : 0),
  total: props.paginator.total ?? 0,
}));

// Build a map of page -> url when backend provides links (Laravel paginator)
const linkMap = computed(() => {
  const map = new Map();
  if (!props.paginator.links || !props.paginator.links.length) return map;

  for (const link of props.paginator.links) {
    if (!link.url) continue;
    try {
      const url = new URL(link.url, window.location.origin);
      const pageParam = url.searchParams.get('page');
      const pageNum = pageParam ? Number(pageParam) : 1;
      if (!Number.isNaN(pageNum)) map.set(pageNum, url.pathname + url.search);
    } catch (_) {
      // ignore malformed
    }
  }
  return map;
});

function pageHref(page) {
  // Prefer backend-provided URL when available
  const map = linkMap.value;
  if (map.has(page)) return map.get(page);

  try {
    const url = new URL(window.location.href);
    if (page === 1) url.searchParams.delete('page');
    else url.searchParams.set('page', String(page));
    return url.pathname + url.search;
  } catch (_) {
    return `?page=${page}`;
  }
}

// Compute the condensed pages: always include 1 and last, plus 2 neighbors before/after current
const pages = computed(() => {
  const current = meta.value.current;
  const last = meta.value.last;
  if (last <= 1) return [];

  const set = new Set();
  set.add(1);

  const start = Math.max(2, current - 2);
  const end = Math.min(last - 1, current + 2);
  for (let p = start; p <= end; p++) set.add(p);

  set.add(last);

  return Array.from(set).sort((a, b) => a - b);
});

const middlePages = computed(() => pages.value.filter(p => p !== 1 && p !== meta.value.last));

function needsLeftEllipsis() {
  return middlePages.value.length > 0 && middlePages.value[0] > 2;
}
function needsRightEllipsis() {
  return middlePages.value.length > 0 && middlePages.value[middlePages.value.length - 1] < meta.value.last - 1;
}

// Filter server links to only show a limited set of numeric pages (1, last, current +/-2)
const filteredLinks = computed(() => {
  const links = props.paginator.links || [];
  const current = meta.value.current;
  const last = meta.value.last;

  // allowed numeric pages
  const allowed = new Set();
  allowed.add(1);
  for (let p = Math.max(2, current - 2); p <= Math.min(last - 1, current + 2); p++) allowed.add(p);
  if (last > 1) allowed.add(last);

  // helper to get numeric page from a link label/url
  function extractPage(link) {
    if (!link.url) return null;
    try {
      const url = new URL(link.url, window.location.origin);
      const p = url.searchParams.get('page');
      return p ? Number(p) : 1;
    } catch (_) {
      return null;
    }
  }

  // first pass: keep non-numeric links (prev/next/ellipses) and numeric links only if allowed
  const out = [];
  for (const link of links) {
    const pageNum = extractPage(link);
    if (pageNum !== null) {
      if (allowed.has(pageNum)) out.push({ ...link, _page: pageNum });
      // else skip
    } else {
      // keep as-is (could be prev/next or existing ellipsis)
      out.push({ ...link });
    }
  }

  // second pass: ensure ellipses exist between numeric gaps
  const final = [];
  let lastNumeric = null;
  for (let i = 0; i < out.length; i++) {
    const item = out[i];
    const isNumeric = typeof item._page === 'number';

    // helper to detect existing ellipsis-like labels
    function isEllipsisLabel(label) {
      if (!label) return false;
      const text = String(label).replace(/<[^>]*>/g, '').trim();
      return text === '…' || text === '...' || text === '&hellip;' || text === '&#8230;';
    }

    if (isNumeric) {
      if (lastNumeric !== null && item._page - lastNumeric > 1) {
        // check if next original item is already an ellipsis -> skip adding our own
        const next = out[i + 1];
        if (!(next && !next._page && isEllipsisLabel(next.label))) {
          const prev = final[final.length - 1];
          if (!prev || (prev && (!prev.label || !isEllipsisLabel(prev.label)))) {
            final.push({ url: null, label: '…' });
          }
        }
      }
      final.push(item);
      lastNumeric = item._page;
    } else {
      // preserve existing ellipsis (label may be '…' or '...') or prev/next link
      final.push(item);
    }
  }

  return final;
});
</script>

<template>
  <!-- If backend provided links (Laravel paginator), render original visual but with filtered numeric pages -->
  <nav v-if="paginator.links && paginator.links.length" class="mt-6 flex items-center justify-between" aria-label="Paginação">
    <div class="text-sm text-slate-500">
      <span v-if="meta.total === 0">Nenhum resultado</span>
      <span v-else>Mostrando {{ meta.from }} a {{ meta.to }} de {{ meta.total }} resultados</span>
    </div>

    <div class="flex flex-wrap gap-2">
      <template v-for="link in filteredLinks" :key="link.url + link.label + (link._page ?? '')">
        <span v-if="!link.url" class="px-3 py-2 text-sm text-slate-400" v-html="link.label" />
        <Link v-else :href="link.url" class="px-3 py-2 text-sm font-semibold text-slate-600 bg-white hover:text-slate-800 hover:bg-slate-100 rounded transition" :class="{ 'text-blue-700 border border-blue-200': link.active }" v-html="link.label" preserve-scroll />
      </template>
    </div>
  </nav>

  <!-- Fallback: condensed pagination with summary when backend links are not available -->
  <nav v-else-if="meta.last > 1" class="mt-6 flex items-center justify-between" aria-label="Paginação">
    <div class="text-sm text-slate-500">
      <span v-if="meta.total === 0">Nenhum resultado</span>
      <span v-else>Mostrando {{ meta.from }} a {{ meta.to }} de {{ meta.total }} resultados</span>
    </div>

    <div class="flex items-center gap-2 pagination-controls">
      <Link v-if="meta.current > 1" :href="pageHref(meta.current - 1)" class="page-btn">‹</Link>
      <span v-else class="page-btn disabled">‹</span>

      <!-- first page -->
      <Link :href="pageHref(1)" :class="['page-square', meta.current === 1 ? 'active' : '']">1</Link>

      <!-- left ellipsis -->
      <span v-if="needsLeftEllipsis()" class="px-2 text-slate-400">…</span>

      <!-- middle pages -->
      <template v-for="p in middlePages" :key="p">
        <Link v-if="meta.current !== p" :href="pageHref(p)" :class="['page-square', meta.current === p ? 'active' : '']">{{ p }}</Link>
        <span v-else class="page-square active">{{ p }}</span>
      </template>

      <!-- right ellipsis -->
      <span v-if="needsRightEllipsis()" class="px-2 text-slate-400">…</span>

      <!-- last page (only render if more than 1) -->
      <Link v-if="meta.last > 1" :href="pageHref(meta.last)" :class="['page-square', meta.current === meta.last ? 'active' : '']">{{ meta.last }}</Link>

      <Link v-if="meta.current < meta.last" :href="pageHref(meta.current + 1)" class="page-btn">›</Link>
      <span v-else class="page-btn disabled">›</span>
    </div>
  </nav>
</template>

<style scoped>
.pagination-controls { /* ensure controls don't wrap awkwardly */ }
.page-square, .page-btn {
  width: 40px;
  height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #cbd5e1; /* same as .form-input */
  border-radius: 0.5rem;
  background: #fff;
  color: #0f172a;
  font-weight: 600;
  text-decoration: none;
}
.page-square.active {
  background: #f1f5f9; /* subtle active bg similar to inputs focus */
  color: #0f172a;
  border-color: #cbd5e1;
}
.page-btn {
  width: 36px; /* slightly smaller for arrows */
  height: 36px;
}
.page-btn.disabled {
  opacity: 0.45;
  pointer-events: none;
}
</style>
