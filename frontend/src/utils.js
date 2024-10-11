export function formatDateString(dateString) {
  if (dateString !== '-') {
    const months = [
      'Jan.',
      'Fév.',
      'Mars',
      'Avril',
      'Mai',
      'Juin',
      'Juil.',
      'Août',
      'Sept.',
      'Oct.',
      'Nov.',
      'Dec.'
    ]

    const date = new Date(dateString)
    const day = date.getDate()
    const month = months[date.getMonth()]
    const year = date.getFullYear()

    return `${day} ${month} ${year}`
  }

  return dateString
}

export function getUserInitials(userName) {
  const names = userName.split(' ')
  return names
    .map((name) => name[0])
    .join('')
    .toUpperCase()
}
