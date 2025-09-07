import darkLogo from "@/assets/logos/dark.svg";
import logo from "@/assets/logos/main.svg";
import Image from "next/image";

export function Logo() {
  return (
    <div className="flex items-center gap-2">
      <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-white font-bold text-lg">
        A
      </div>
      <div className="text-lg font-bold text-dark dark:text-white">
        AbsensiQR
      </div>
    </div>
  );
}
