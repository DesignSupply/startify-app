export function formatDate(
  dateString: string | Date,
  options: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'short', day: 'numeric' },
  locale = 'ja-JP',
) {
  return new Intl.DateTimeFormat(locale, options).format(new Date(dateString));
}
