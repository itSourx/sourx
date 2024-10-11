import {
  PlusCircle,
  Clock,
  Search,
  Download,
  ArrowLeftRight,
  Trash,
  FolderPlus,
  EyeIcon,
  DownloadIcon,
  PaperclipIcon,
  FileText,
  Mail,
  Eye,
  EyeOffIcon,
  SearchIcon,
  PlusCircleIcon,
  CircleX,
  LogOut,
  Clock2,
  UserRound,
  FolderLock,
  Hourglass,
  CircleCheckBig,
  ClipboardList,
  FileStack,
  Filter,
  History,
  ArrowUpNarrowWide,
  ArrowDownNarrowWide,
  Replace,
  StepBack,
  StepForward,
  BadgeCheck,
  Edit3,
  PhoneCall,
  Users,
  LockKeyhole,
  Pencil,
  CheckCircle,
  User,
  EllipsisVertical,
  Send,
  Notebook
} from 'lucide-vue-next'

const icons = {
  PlusCircle,
  Clock,
  Search,
  Download,
  ArrowLeftRight,
  Trash,
  FolderPlus,
  EyeIcon,
  DownloadIcon,
  PaperclipIcon,
  FileText,
  Mail,
  Eye,
  EyeOffIcon,
  SearchIcon,
  PlusCircleIcon,
  CircleX,
  LogOut,
  Clock2,
  UserRound,
  FolderLock,
  Hourglass,
  CircleCheckBig,
  ClipboardList,
  FileStack,
  Filter,
  History,
  ArrowUpNarrowWide,
  ArrowDownNarrowWide,
  Replace,
  StepBack,
  StepForward,
  BadgeCheck,
  Edit3,
  PhoneCall,
  Users,
  LockKeyhole,
  Pencil,
  CheckCircle,
  User,
  EllipsisVertical,
  Send,
  Notebook
}

export default {
  install(app) {
    Object.entries(icons).forEach(([name, component]) => {
      app.component(name, component)
    })
  }
}
